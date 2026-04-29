<?php

namespace Woocommerce\Pagarme\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Helper\Utils;
use PHPUnit\Framework\Attributes\DataProvider;

class UtilsTests extends TestCase
{
    #[DataProvider('onlyNumbersProvider')]
    public function only_numbers_WithVariousInputs_ShouldReturnOnlyDigits(string $input, string $expected)
    {
        // Arrange & Act
        $result = Utils::only_numbers($input);

        // Assert
        $this->assertEquals($expected, $result, "Input '{$input}' should return '{$expected}'");
    }

    #[DataProvider('onlyAlphanumericProvider')]
    public function only_alphanumeric_WithVariousInputs_ShouldReturnOnlyAlphanumericCharacters(string $input, string $expected)
    {
        // Arrange & Act
        $result = Utils::only_alphanumeric($input);

        // Assert
        $this->assertEquals($expected, $result, "Input '{$input}' should return '{$expected}'");
    }

    #[DataProvider('equalCharactersProvider')]
    public function hasAllEqualCharacters_WithAllSameCharacters_ShouldReturnTrue(string $input)
    {
        // Arrange & Act
        $result = Utils::hasAllEqualCharacters($input);

        // Assert
        $this->assertTrue($result, "Input '{$input}' should have all equal characters");
    }

    #[DataProvider('differentCharactersProvider')]
    public function hasAllEqualCharacters_WithDifferentCharacters_ShouldReturnFalse(string $input)
    {
        // Arrange & Act
        $result = Utils::hasAllEqualCharacters($input);

        // Assert
        $this->assertFalse($result, "Input '{$input}' should NOT have all equal characters");
    }

    public static function onlyNumbersProvider(): array
    {
        return [
            // Clean numeric strings
            'Only digits' => ['12345', '12345'],
            'Only zeros' => ['00000', '00000'],
            'Mixed digits' => ['9876543210', '9876543210'],
            
            // Formatted strings
            'CPF formatted' => ['111.444.777-35', '11144477735'],
            'CNPJ formatted' => ['11.222.333/0001-81', '11222333000181'],
            'Phone formatted' => ['(11) 98765-4321', '11987654321'],
            'With spaces' => ['123 456 789', '123456789'],
            
            // Mixed alphanumeric
            'Letters and numbers' => ['ABC123DEF456', '123456'],
            'Numbers with symbols' => ['12@34#56$78', '12345678'],
            'Complex mix' => ['A1B2C3-D4.E5/F6', '123456'],
            
            // Special cases
            'Empty string' => ['', ''],
            'Only letters' => ['ABCDEF', ''],
            'Only symbols' => ['@#$%&*', ''],
            'Only spaces' => ['     ', ''],
            
            // Edge cases
            'Leading zeros' => ['00123', '00123'],
            'Trailing spaces with numbers' => ['123   ', '123'],
            'Mixed formatting' => ['R$ 1.234,56', '123456'],
        ];
    }

    public static function onlyAlphanumericProvider(): array
    {
        return [
            // Clean alphanumeric strings
            'Only letters uppercase' => ['ABCDEF', 'ABCDEF'],
            'Only letters lowercase' => ['abcdef', 'abcdef'],
            'Only digits' => ['123456', '123456'],
            'Mixed case letters' => ['AbCdEf', 'AbCdEf'],
            'Letters and numbers' => ['ABC123def456', 'ABC123def456'],
            
            // Formatted strings
            'CPF formatted' => ['111.444.777-35', '11144477735'],
            'CNPJ formatted' => ['11.222.333/0001-81', '11222333000181'],
            'With spaces' => ['ABC 123 DEF', 'ABC123DEF'],
            'With dashes' => ['ABC-123-DEF', 'ABC123DEF'],
            
            // Mixed with symbols
            'Numbers with symbols' => ['12@34#56$78', '12345678'],
            'Letters with symbols' => ['A@B#C$D', 'ABCD'],
            'Complex mix' => ['A1-B2.C3/D4', 'A1B2C3D4'],
            'Email format' => ['test@email.com', 'testemailcom'],
            
            // Special cases
            'Empty string' => ['', ''],
            'Only symbols' => ['@#$%&*', ''],
            'Only spaces' => ['     ', ''],
            'Symbols between chars' => ['A!B@C#D', 'ABCD'],
            
            // Edge cases
            'Alphanumeric CNPJ' => ['12.ABC.345/01DE-35', '12ABC34501DE35'],
            'Mixed everything' => ['A1!b2@C3#d4$', 'A1b2C3d4'],
            'Unicode preserved' => ['ABC123', 'ABC123'],
        ];
    }

    public static function equalCharactersProvider(): array
    {
        return [
            // Single character repeated
            'All zeros' => ['0000000000'],
            'All ones' => ['1111111111'],
            'All letter A uppercase' => ['AAAAAAA'],
            'All letter a lowercase' => ['aaaaaaa'],
            'All letter Z uppercase' => ['ZZZZZ'],
            
            // Different lengths
            'Two same chars' => ['11'],
            'Three same chars' => ['333'],
            'Five same chars' => ['BBBBB'],
            'Ten same chars' => ['9999999999'],
            
            // Single character
            'Single digit' => ['5'],
            'Single letter' => ['X'],
        ];
    }

    public static function differentCharactersProvider(): array
    {
        return [
            // Empty string
            'Empty string' => [''],
            
            // Mixed characters
            'Two different digits' => ['12'],
            'Mixed digits' => ['1234567890'],
            'Mixed letters uppercase' => ['ABCDEFG'],
            'Mixed letters lowercase' => ['abcdefg'],
            'Mixed alphanumeric' => ['A1B2C3'],
            
            // Mostly same with one different
            'All zeros but one' => ['0000100000'],
            'All As but one' => ['AAAABAAA'],
            'All 9s but one' => ['99999999989'],
            
            // Common patterns
            'Sequential numbers' => ['123456'],
            'Repeated pattern' => ['121212'],
            'Valid CPF' => ['11144477735'],
            'Valid CNPJ' => ['11222333000181'],
            
            // Special cases
            'Two chars different' => ['ab'],
            'Three chars all different' => ['abc'],
            'Alphanumeric mix' => ['A1A1A1'],
            'Case sensitive different' => ['AAAaAAA'],
            
            // Edge cases with special chars (after cleaning)
            'Mixed with symbols' => ['111-222-333'],
            'Formatted document' => ['123.456.789-00'],
        ];
    }
}