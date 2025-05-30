<?php

namespace Application\Validator;

class TextoValidator
{
    public static function validar(string $texto, string $campo, string $regex, bool $obrigatorio = true): void
    {
        $texto = trim($texto);

        if ($obrigatorio && $texto === '') {
            throw new \InvalidArgumentException("O campo {$campo} é obrigatório.");
        }

        if ($texto !== '' && !preg_match($regex, $texto)) {
            throw new \InvalidArgumentException("O campo {$campo} contém caracteres inválidos.");
        }
    }
}