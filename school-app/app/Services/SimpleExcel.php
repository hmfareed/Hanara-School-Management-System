<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SimpleExcel
{
    /**
     * Load a file.
     *
     * @param string $filePath
     * @return object
     */
    public function load(string $filePath)
    {
        return new class($filePath) {
            private string $filePath;

            public function __construct(string $filePath)
            {
                $this->filePath = $filePath;
            }

            /**
             * Get collection of rows.
             *
             * @return Collection
             */
            public function get()
            {
                $rows = [];
                if (($handle = fopen($this->filePath, 'r')) !== false) {
                    $headers = fgetcsv($handle, 1000, ',');
                    if ($headers !== false) {
                        // Clean headers (remove BOM, trim, lower case, replace spaces with underscores)
                        $headers = array_map(function($header) {
                            $header = preg_replace('/[\x{FEFF}\x{FFFE}]/u', '', $header);
                            // Replace non-alphanumeric chars with underscores, collapse multiple underscores, trim
                            $header = preg_replace('/[^a-zA-Z0-9]/', '_', $header);
                            $header = preg_replace('/_+/', '_', $header);
                            return strtolower(trim($header, '_'));
                        }, $headers);

                        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                            // If row is empty, skip
                            if (empty(array_filter($data))) {
                                continue;
                            }
                            
                            // Pad or truncate data array to match headers length
                            if (count($data) < count($headers)) {
                                $data = array_pad($data, count($headers), '');
                            } elseif (count($data) > count($headers)) {
                                $data = array_slice($data, 0, count($headers));
                            }

                            $row = array_combine($headers, $data);
                            $rows[] = new class($row) implements \ArrayAccess {
                                private array $data;

                                public function __construct(array $data)
                                {
                                    $this->data = $data;
                                }

                                public function __get(string $key)
                                {
                                    return $this->data[$key] ?? null;
                                }

                                public function __set(string $key, $value): void
                                {
                                    $this->data[$key] = $value;
                                }

                                public function toArray(): array
                                {
                                    return $this->data;
                                }

                                public function offsetExists($offset): bool
                                {
                                    return isset($this->data[$offset]);
                                }

                                public function offsetGet($offset): mixed
                                {
                                    return $this->data[$offset] ?? null;
                                }

                                public function offsetSet($offset, $value): void
                                {
                                    $this->data[$offset] = $value;
                                }

                                public function offsetUnset($offset): void
                                {
                                    unset($this->data[$offset]);
                                }
                            };
                        }
                    }
                    fclose($handle);
                }
                return new Collection($rows);
            }
        };
    }
}
