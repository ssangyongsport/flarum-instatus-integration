<?php

namespace Maicol07\Instatus;

use Carbon\Carbon;
use Illuminate\Support\Str;

abstract class Base
{
    public function __construct(array|object $body) {
        foreach ($body as $prop => $value) {
            $prop = Str::camel($prop);
            $this->preTransform($prop, $value);

            if (is_object($value)) {
                $partial = 'Maicol07\Instatus\Partials\\' . Str::studly($prop);
                $value = new $partial($value);
            }

            if (is_array($value)) {
                $partial = 'Maicol07\Instatus\Partials\\' . Str::studly(Str::singular($prop));
                $collection = collect();
                foreach ($value as $obj) {
                    $collection->push(new $partial($obj));
                }
                $value = $collection;
            }

            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}).(\d{3})Z$/', $value)) {
                $value = Carbon::parse($value);
            }
            $this->postTransform($prop, $value);

            $this->$prop = $value;
        }
    }

    /**
     * Executed before object check (after camel case conversion)
     *
     * @param string $prop
     * @param mixed $value
     */
    public function preTransform(string &$prop, mixed &$value): void {}

    /**
     * Executed before prop assignment
     *
     * @param string $prop
     * @param mixed $value
     */
    public function postTransform(string &$prop, mixed &$value): void {}

    public function __call(string $name, array $arguments=null): mixed
    {
        return $this?->$name;
    }
}