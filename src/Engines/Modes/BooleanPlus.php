<?php

namespace Yab\MySQLScout\Engines\Modes;

use Laravel\Scout\Builder;

class BooleanPlus extends Mode
{
    public function buildWhereRawString(Builder $builder)
    {
        $queryString = '';
        
        $queryString .= $this->buildWheres($builder);
        
        $indexFields = implode(',',  $this->modelService->setModel($builder->model)->getFullTextIndexFields());
        
        $queryString .= "MATCH($indexFields) AGAINST(? IN BOOLEAN MODE)";
        
        return $queryString;
    }
    
    public function buildSelectColumns(Builder $builder)
    {
        $indexFields = implode(',',  $this->modelService->setModel($builder->model)->getFullTextIndexFields());
        
        return "*, MATCH($indexFields) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance";
    }
    
    public function buildParams(Builder $builder)
    {
        $this->whereParams[] = $this->fullTextWildcards($builder->query);
        
        return $this->whereParams;
    }
    
    protected function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);
        
        $words = explode(' ', $term);
        
        foreach ($words as $key => $word) {
            if (strlen($word) >= 2) {
                $words[$key] = $word . '*';
            }
        }
    
        return implode(' ', $words);
    }
    
    public function isFullText()
    {
        return true;
    }
}
