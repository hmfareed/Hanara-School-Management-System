<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Excel;

class StudentsImport
{
    protected array $errors = [];
    protected int $successCount = 0;

    /**
     * Get import validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get success count.
     *
     * @return int
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Process the import file.
     *
     * @param string $filePath
     * @return bool Returns true if successful, false otherwise.
     */
    public function import(string $filePath): bool
    {
        $this->errors = [];
        $this->successCount = 0;

        $currentYear = AcademicYear::where('is_current', true)->first();
        if (!$currentYear) {
            $this->errors[] = 'No active academic year configured. Please set up the current year in Settings first.';
            return false;
        }

        try {
            $rows = Excel::load($filePath)->get();
        } catch (\Exception $e) {
            $this->errors[] = 'Failed to read the file: ' . $e->getMessage();
            return false;
        }

        if ($rows->isEmpty()) {
            $this->errors[] = 'The uploaded file is empty or could not be parsed.';
            return false;
        }

        // 1. First Pass: Validate all rows before inserting any data
        $validatedRows = [];
        $rowNum = 1; // 1-indexed count for user-friendly error reporting (header is row 1, so data starts at row 2)

        foreach ($rows as $row) {
            $rowNum++;
            $data = $row->toArray();

            $validator = Validator::make($data, [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'other_names' => ['nullable', 'string', 'max:255'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'gender' => ['required', 'in:male,female'],
                'class' => ['required', 'string'],
                'guardian_first_name' => ['required', 'string', 'max:255'],
                'guardian_last_name' => ['required', 'string', 'max:255'],
                'guardian_phone' => ['required', 'string', 'max:20'],
                'guardian_relationship' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->errors[] = "Row {$rowNum} ({$row->first_name} {$row->last_name}): {$error}";
                }
                continue;
            }

            // Find class
            $className = trim($row->class);
            $schoolClass = SchoolClass::where('name', 'like', $className)->first();
            if (!$schoolClass) {
                $this->errors[] = "Row {$rowNum}: Class '{$className}' does not exist in the system.";
                continue;
            }

            // Find ClassAcademicYear mapping
            $classAcYear = ClassAcademicYear::where('school_class_id', $schoolClass->id)
                ->where('academic_year_id', $currentYear->id)
                ->first();

            if (!$classAcYear) {
                $this->errors[] = "Row {$rowNum}: Class '{$className}' is not active for the current academic year ({$currentYear->name}).";
                continue;
            }

            $validatedRows[] = [
                'row_data' => $row,
                'class_academic_year_id' => $classAcYear->id,
            ];
        }

        // If there are validation errors, do not proceed with import
        if (!empty($this->errors)) {
            return false;
        }

        // 2. Second Pass: Insert data within a transaction
        DB::beginTransaction();

        try {
            foreach ($validatedRows as $validated) {
                $row = $validated['row_data'];
                $classAcYearId = $validated['class_academic_year_id'];

                // 2.1 Find or Create Guardian (by phone number matching)
                $phone = trim($row->guardian_phone);
                $guardian = Guardian::where('phone', $phone)->first();

                if (!$guardian) {
                    $guardian = Guardian::create([
                        'first_name' => trim($row->guardian_first_name),
                        'last_name' => trim($row->guardian_last_name),
                        'phone' => $phone,
                        'relationship' => trim($row->guardian_relationship),
                        'is_emergency_contact' => true,
                    ]);
                }

                // 2.2 Create Student
                $student = Student::create([
                    'student_id_number' => Student::generateStudentId(),
                    'first_name' => trim($row->first_name),
                    'last_name' => trim($row->last_name),
                    'other_names' => $row->other_names ? trim($row->other_names) : null,
                    'date_of_birth' => $row->date_of_birth,
                    'gender' => strtolower(trim($row->gender)),
                    'nationality' => 'Ghanaian',
                    'admission_date' => now(),
                    'status' => 'active',
                ]);

                // 2.3 Connect Student and Guardian
                $student->guardians()->attach($guardian->id, ['is_primary' => true]);

                // 2.4 Enroll Student in ClassAcademicYear
                ClassStudent::create([
                    'student_id' => $student->id,
                    'class_academic_year_id' => $classAcYearId,
                    'enrolled_at' => now(),
                    'status' => 'enrolled',
                ]);

                $this->successCount++;
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = 'Import failed due to a database error: ' . $e->getMessage();
            return false;
        }
    }
}
