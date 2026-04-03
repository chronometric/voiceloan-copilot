<?php

namespace App\Services\Urla1003;

use App\Models\Borrower;

class Urla1003FieldResolver
{
    public function value(Borrower $borrower, string $path): mixed
    {
        $borrower->loadMissing(['identity', 'employments', 'assets', 'declaration']);

        $parts = explode('.', $path);
        $root = array_shift($parts);

        return match ($root) {
            'borrower' => $this->getBorrowerAttr($borrower, $parts),
            'identity' => $this->getIdentityAttr($borrower, $parts),
            'employment' => $this->getEmploymentAttr($borrower, $parts),
            'assets' => $this->getAssetAttr($borrower, $parts),
            'declaration' => $this->getDeclarationAttr($borrower, $parts),
            default => null,
        };
    }

    public function isMissing(Borrower $borrower, string $path): bool
    {
        $v = $this->value($borrower, $path);

        if ($v === null) {
            return true;
        }
        if (is_bool($v)) {
            return false;
        }
        if (is_string($v) && trim($v) === '') {
            return true;
        }
        if ($v === '') {
            return true;
        }

        return false;
    }

    /**
     * @param  list<string>  $parts
     */
    private function getBorrowerAttr(Borrower $borrower, array $parts): mixed
    {
        $key = implode('.', $parts);
        if ($key === '') {
            return null;
        }

        return $borrower->getAttribute($key);
    }

    /**
     * @param  list<string>  $parts
     */
    private function getIdentityAttr(Borrower $borrower, array $parts): mixed
    {
        $identity = $borrower->identity;
        if ($identity === null) {
            return null;
        }
        $key = implode('.', $parts);

        return $key === '' ? null : $identity->getAttribute($key);
    }

    /**
     * @param  list<string>  $parts  e.g. ['0','employer_name']
     */
    private function getEmploymentAttr(Borrower $borrower, array $parts): mixed
    {
        $index = (int) array_shift($parts);
        $key = implode('.', $parts);
        $row = $borrower->employments()->orderBy('id')->skip($index)->first();

        return $row && $key !== '' ? $row->getAttribute($key) : null;
    }

    /**
     * @param  list<string>  $parts
     */
    private function getAssetAttr(Borrower $borrower, array $parts): mixed
    {
        $index = (int) array_shift($parts);
        $key = implode('.', $parts);
        $row = $borrower->assets()->orderBy('id')->skip($index)->first();

        return $row && $key !== '' ? $row->getAttribute($key) : null;
    }

    /**
     * @param  list<string>  $parts
     */
    private function getDeclarationAttr(Borrower $borrower, array $parts): mixed
    {
        $decl = $borrower->declaration;
        if ($decl === null) {
            return null;
        }
        $key = implode('.', $parts);

        return $key === '' ? null : $decl->getAttribute($key);
    }
}
