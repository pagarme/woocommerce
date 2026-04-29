<?php

namespace Woocommerce\Pagarme\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Helper\DocumentUtils;
use PHPUnit\Framework\Attributes\DataProvider;

class DocumentUtilsTests extends TestCase
{
    #[DataProvider('validCnpjProvider')]
    public function isValidCnpj_WithValidCnpj_ShouldReturnTrue(string $validCnpj)
    {
        // Arrange & Act
        $result = DocumentUtils::isValidCnpj($validCnpj);

        // Assert
        $this->assertTrue($result, "CNPJ '{$validCnpj}' should be valid");
    }

    #[DataProvider('invalidCnpjProvider')]
    public function isValidCnpj_WithInvalidCnpj_ShouldReturnFalse(string $invalidCnpj)
    {
        // Arrange & Act
        $result = DocumentUtils::isValidCnpj($invalidCnpj);

        // Assert
        $this->assertFalse($result, "CNPJ '{$invalidCnpj}' should be invalid");
    }

    #[DataProvider('validCpfProvider')]
    public function isValidCpf_WithValidCpf_ShouldReturnTrue(string $validCpf)
    {
        // Arrange & Act
        $result = DocumentUtils::isValidCpf($validCpf);

        // Assert
        $this->assertTrue($result, "CPF '{$validCpf}' should be valid");
    }

    #[DataProvider('invalidCpfProvider')]
    public function isValidCpf_WithInvalidCpf_ShouldReturnFalse(string $invalidCpf)
    {
        // Arrange & Act
        $result = DocumentUtils::isValidCpf($invalidCpf);

        // Assert
        $this->assertFalse($result, "CPF '{$invalidCpf}' should be invalid");
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

}