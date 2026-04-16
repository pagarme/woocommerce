<?php

namespace Woocommerce\Pagarme\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Helper\Utils;
use PHPUnit\Framework\Attributes\DataProvider;

class UtilsTests extends TestCase
{
    #[DataProvider('validCnpjProvider')]
    public function isValidCnpj_WithValidCnpj_ShouldReturnTrue(string $validCnpj)
    {
        // Arrange & Act
        $result = Utils::isValidCnpj($validCnpj);

        // Assert
        $this->assertTrue($result, "CNPJ '{$validCnpj}' should be valid");
    }

    #[DataProvider('invalidCnpjProvider')]
    public function isValidCnpj_WithInvalidCnpj_ShouldReturnFalse(string $invalidCnpj)
    {
        // Arrange & Act
        $result = Utils::isValidCnpj($invalidCnpj);

        // Assert
        $this->assertFalse($result, "CNPJ '{$invalidCnpj}' should be invalid");
    }

    #[DataProvider('validCpfProvider')]
    public function isValidCpf_WithValidCpf_ShouldReturnTrue(string $validCpf)
    {
        // Arrange & Act
        $result = Utils::isValidCpf($validCpf);

        // Assert
        $this->assertTrue($result, "CPF '{$validCpf}' should be valid");
    }

    #[DataProvider('invalidCpfProvider')]
    public function isValidCpf_WithInvalidCpf_ShouldReturnFalse(string $invalidCpf)
    {
        // Arrange & Act
        $result = Utils::isValidCpf($invalidCpf);

        // Assert
        $this->assertFalse($result, "CPF '{$invalidCpf}' should be invalid");
    }

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

    public static function validCnpjProvider(): array
    {
        return [
            // Numeric CNPJs without formatting
            'Valid numeric CNPJ 1' => ['11222333000181'],
            'Valid numeric CNPJ 2' => ['34028316000103'],
            'Valid numeric CNPJ 3' => ['00360305000104'],
            'Valid numeric CNPJ 4' => ['41381074000155'],
            
            // CNPJs with standard formatting (dots, slash, dash)
            'Valid CNPJ with formatting 1' => ['11.222.333/0001-81'],
            'Valid CNPJ with formatting 2' => ['34.028.316/0001-03'],
            
            // CNPJs with spaces
            'Valid CNPJ with spaces' => ['11 222 333 0001 81'],
            
            // CNPJs with leading/trailing whitespace
            'Valid CNPJ with leading and trailing whitespace' => ['  11.222.333/0001-81  '],
            'Valid CNPJ with mixed formatting and spaces' => ['11.222.333/0001-81  '],

            // CNPJs with alphanumeric
            'Valid Alfanumeric CNPJ 1' => ['12ABC34501DE35'],
            'Valid Alfanumeric CNPJ 2' => ['12.ABC.345/01DE-35'],
            'Valid Alfanumeric CNPJ 3' => ['12abc34501de35'],
        ];
    }

    public static function invalidCnpjProvider(): array
    {
        return [
            // Empty and null values
            'Empty string' => [''],
            'Whitespace value' => ['  '],
            'Null value' => [null],
 
            // Wrong length
            'Less than 14 digits' => ['112223330001'],
            'Only 13 digits' => ['1122233300018'],
            'More than 14 digits' => ['112223330001811'],
            'Way too short' => ['123'],
            
            // Wrong check digits
            'Wrong first check digit' => ['11222333000171'],
            'Wrong second check digit' => ['11222333000180'],
            'Both check digits wrong' => ['11222333000100'],
            'Random wrong check digits' => ['34028316000199'],
            
            // All same characters (repeated digits)
            'All zeros' => ['00000000000000'],
            'All ones' => ['11111111111111'],
            'All twos' => ['22222222222222'],
            'All threes' => ['33333333333333'],
            'All fours' => ['44444444444444'],
            'All fives' => ['55555555555555'],
            'All sixes' => ['66666666666666'],
            'All sevens' => ['77777777777777'],
            'All eights' => ['88888888888888'],
            'All nines' => ['99999999999999'],
            'All same letters uppercase' => ['AAAAAAAAAAAAAA'],
            'All same letters lowercase' => ['bbbbbbbbbbbbbb'],
            
            // Invalid characters/format
            'Only special characters' => ['../../----/////'],
            'Invalid special characters mixed' => ['11@222#333$0001%81'],
            'With invalid symbols' => ['11*222&333!0001^81'],
            
            // Alphanumeric with invalid check digits
            'Alphanumeric uppercase invalid' => ['ABC12345678999'],
            'Alphanumeric lowercase invalid' => ['abc12345678999'],
            'Mixed alphanumeric invalid' => ['AbC12345678DeF'],
            
            // Partially valid format but invalid content
            'Valid format but sequential numbers' => ['12345678901234'],
            'Valid length but all characters invalid' => ['##############'],
        ];
    }

    public static function validCpfProvider(): array
    {
        return [
            // Numeric CPFs without formatting
            'Valid numeric CPF 1' => ['11144477735'],
            'Valid numeric CPF 2' => ['52998224725'],
            'Valid numeric CPF 3' => ['39053344705'],
            'Valid numeric CPF 4' => ['84841848409'],
            'Valid numeric CPF 5' => ['04390999001'],
            
            // CPFs with standard formatting (dots and dash)
            'Valid CPF with formatting 1' => ['111.444.777-35'],
            'Valid CPF with formatting 2' => ['529.982.247-25'],
            'Valid CPF with formatting 3' => ['390.533.447-05'],
            
            // CPFs with spaces
            'Valid CPF with spaces' => ['111 444 777 35'],
            
            // CPFs with leading/trailing whitespace
            'Valid CPF with leading and trailing whitespace' => ['  111.444.777-35  '],
            'Valid CPF with mixed formatting and spaces' => ['111.444.777-35  '],
            
            // CPFs with various special characters
            'Valid CPF with slashes' => ['111/444/777/35'],
        ];
    }

    public static function invalidCpfProvider(): array
    {
        return [
            // Empty and null values
            'Empty string' => [''],
            'Whitespace value' => ['  '],
            'Null value' => [null],
 
            // Wrong length
            'Less than 11 digits' => ['1114447773'],
            'Only 10 digits' => ['1114447773'],
            'More than 11 digits' => ['111444777355'],
            'Only 9 digits' => ['111444777'],
            'Way too short' => ['123'],
            
            // Wrong check digits
            'Wrong first check digit' => ['11144477745'],
            'Wrong second check digit' => ['11144477734'],
            'Both check digits wrong' => ['11144477700'],
            'Random wrong check digits' => ['52998224799'],
            
            // All same characters (repeated digits)
            'All zeros' => ['00000000000'],
            'All ones' => ['11111111111'],
            'All twos' => ['22222222222'],
            'All threes' => ['33333333333'],
            'All fours' => ['44444444444'],
            'All fives' => ['55555555555'],
            'All sixes' => ['66666666666'],
            'All sevens' => ['77777777777'],
            'All eights' => ['88888888888'],
            'All nines' => ['99999999999'],
            
            // Invalid characters/format
            'Only special characters' => ['...--.--///'],
            'Invalid special characters mixed' => ['111@444#777$35'],
            'With invalid symbols' => ['111*444&777!35'],
            
            // Alphanumeric (CPF accepts only numbers)
            'Alphanumeric uppercase' => ['ABC44477735'],
            'Alphanumeric lowercase' => ['abc44477735'],
            'Mixed alphanumeric' => ['11A444B7735'],
            'All letters' => ['ABCDEFGHIJK'],
            
            // Partially valid format but invalid content
            'Valid format but sequential numbers' => ['12345678901'],
            'Valid length but all characters invalid' => ['###########'],
        ];
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