<?php

namespace App\Traits;

trait UserScopes
{
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) use ($filters) {
            if ($filters['match'] == 'exact') {
                return $this->exact($query, $search);
            }
            if ($filters['match'] == 'accurate') {
                return $this->accurate($query, $search);
            }
            if ($filters['match'] == 'approximate') {
                return $this->approximate($query, $search);
            }
        });
    }

    private function exact($query, $search)
    {
        return $query->where('name', 'LIKE', '%' . $search . '%')
            ->orderByRaw("CASE 
                    WHEN name LIKE '" . $search . "' THEN 0 
                    WHEN name LIKE '" . $search . "%' THEN 1 
                    WHEN name LIKE '%" . $search . "' THEN 2 
                    WHEN name LIKE '%" . $search . "%' THEN 3 
                    ELSE 4 END, name");
    }

    private function accurate($query, $search)
    {
        $words = preg_split('/\s+/u', $search);
        $search = implode('%', $words);
        $rawChars = [];
        foreach ($words as $word) {
            $mbChars = mb_str_split($word);
            $len = count($mbChars);
            if ($len > 1) {
                $lastChar = end($mbChars);
                $mbChars[$len - 1] = preg_replace('/^(ا|ة|ه)$/u', '(ى|ه|ة|ا)', $lastChar);
            }
            array_push($rawChars, ...$mbChars);
            array_push($rawChars, '(.+)?');
        }

        $regChars = preg_replace('/^(ا|أ|إ|آ)$/u', '(ا|أ|إ|آ|ى|ئ)', $rawChars);
        $regChars = preg_replace('/^(و|ؤ)$/u', '(وؤ|ؤ|ؤو|ءو|وو|و)', $regChars);
        $regChars = preg_replace('/^(ي|يء)$/u', '(ى|ئ|ي|يء)', $regChars);
        $regChars = preg_replace('/^(ى|ئ)$/u', '(ى|ئ|ي|يء|ا|أ)', $regChars);
        $regChars = preg_replace('/^(ء)$/u', '(ء|ئ|ؤ|أ)', $regChars);

        $term = implode('', $regChars);
        $regTerm = "/^(.+)?" . $term . "(.+)?$/u";

        return $query->where('name', 'REGEXP', $regTerm)
            ->orderByRaw("CASE 
                    WHEN name LIKE '" . $search . "' THEN 0 
                    WHEN name LIKE '" . $search . "%' THEN 1 
                    WHEN name LIKE '%" . $search . "' THEN 2 
                    WHEN name LIKE '%" . $search . "%' THEN 3 
                    WHEN name REGEXP '/^" . $term . "$/u' THEN 4 
                    WHEN name REGEXP '/^" . $term . "(.+)?$/u' THEN 5 
                    WHEN name REGEXP '/^(.+)?" . $term . "$/u' THEN 6 
                    ELSE 7 END, name");
    }

    private function approximate($query, $search)
    {
        $rawChars = mb_str_split($search);

        $regChars = preg_replace('/^(و|ؤ|ا|ة|ه|ا|أ|إ|آ|ي|يء|ى|ئ|ء|\s)$/u', '(.+)?', $rawChars);

        $term = implode('', $regChars);
        $regTerm = "/^(.+)?" . $term . "(.+)?$/u";

        return $query->where('name', 'REGEXP', $regTerm)
            ->orderByRaw("CASE 
                    WHEN name LIKE '%" . $search . "%' THEN 0 
                    WHEN name REGEXP '/^(.+)?" . $term . "$/u' THEN 1 
                    ELSE 2 END, name");
    }
}
