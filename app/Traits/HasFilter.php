<?php

namespace App\Traits;

trait HasFilter
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
                    ELSE 3 END, name");
    }

    private function accurate($query, $search)
    {
        $term = '';
        $rawChars = mb_str_split($search);
        $len = count($rawChars);

        foreach ($rawChars as $key => $char) {
            switch ($char) {
                case 'ا':
                case 'ة':
                case 'ه':
                    if ($key + 1 < $len) {
                        if (ctype_space($rawChars[$key + 1])) {
                            // الحرف في وسط الجملة وبعده فراغ (نهاية كلمة)
                            $term .= '(ا|ة|ه|ى)';
                            break;
                        }
                    } elseif ($len > 1) {
                        // الجملة حرفين فأكثر والحرف في آخرها (نهاية كلمة)
                        $term .= '(ا|ة|ه|ى)';
                        break;
                    }

                case 'ا':
                case 'أ':
                case 'إ':
                case 'آ':
                    $term .= '(ا|أ|إ|آ|ؤ|ئ|ء)';
                    break;

                case 'و':
                case 'ؤ':
                    if ($len > 1 && $key > 0) {
                        if (preg_match('/(.*)(و|ؤ|ء)(.*)/u', $rawChars[$key - 1])) {
                            // الجملة حرفين فأكثر ويوجد قبل الهمزة أحد الحروف (ا ي ى)
                            $term .= '(.*)';
                            break;
                        }
                    }
                    $term .= '(و|وو|ؤ|ؤؤ|وؤ|ؤو|ءو|وء|ء)';
                    break;

                case 'ى':
                case 'ئ':
                    $term .= '(ى|ئ|ي|يء|ا|أ|ء)';
                    break;

                case 'ي':
                    $term .= '(ى|ئ|ي|يء|ء)';
                    break;

                case 'ء':
                    if ($len > 1 && $key > 0) {
                        if (preg_match('/(.*)(ا|ي|ى)(.*)/u', $rawChars[$key - 1])) {
                            // الجملة حرفين فأكثر ويوجد قبل الهمزة أحد الحروف (ا ي ى)
                            $term .= '(.*)';
                            break;
                        }
                    }
                    $term .= '(أ|إ|آ|ا|ؤ|ئ|ء)';
                    break;

                default:
                    if (ctype_space($char)) {
                        $term .= '(.*)';
                        break;
                    } else {
                        $term .= $char;
                        break;
                    }
            }
        }

        $regTerm = "(.*)" . $term . "(.*)";

        return $query->where('name', 'REGEXP', $regTerm)
            ->orderByRaw("CASE 
                    WHEN name LIKE '" . $search . "' THEN 0 
                    WHEN name LIKE '" . $search . "%' THEN 1 
                    WHEN name LIKE '%" . $search . "' THEN 2 
                    WHEN name LIKE '%" . $search . "%' THEN 3 
                    WHEN name REGEXP '" . $term . "' THEN 4 
                    WHEN name REGEXP '" . $term . "(.*)' THEN 5 
                    WHEN name REGEXP '(.*)" . $term . "' THEN 6 
                    ELSE 7 END");
    }

    private function approximate($query, $search)
    {
        $searchTerm = preg_replace('/(و|ؤ|ة|ه|ا|أ|إ|آ|ي|يء|ى|ئ|ء|\s)/u', '%', $search);

        return $query->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->orderByRaw("CASE 
                WHEN name LIKE '" . $searchTerm . "' THEN 0 
                WHEN name LIKE '" . $searchTerm . "%' THEN 1 
                WHEN name LIKE '%" . $searchTerm . "' THEN 2 
                ELSE 3 END");
    }
}
