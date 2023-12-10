<?php

namespace Spin8\Tests\Unit\WP\Settings\Enums;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Tests\TestCase;
use Spin8\WP\Settings\Enums\SettingTypes;

#[CoversClass(SettingTypes::class)]
final class SettingTypesTest extends TestCase {

    #[Test]
    public function test_sanitizeCallback_method_returns_correct_sanitizer_function_string_name(): void {        
        $this->assertEquals("sanitize_text_field", SettingTypes::STRING->sanitizeCallback());
        $this->assertEquals("sanitize_text_field", SettingTypes::BOOL->sanitizeCallback());
        $this->assertEquals("sanitize_text_field", SettingTypes::INT->sanitizeCallback());
        $this->assertEquals("sanitize_text_field", SettingTypes::NUMBER->sanitizeCallback());
        $this->assertEquals("sanitize_text_field", SettingTypes::SELECT->sanitizeCallback());

        $this->assertEquals("sanitize_hex_color", SettingTypes::COLOR->sanitizeCallback());
        
        $this->assertEquals("sanitize_email", SettingTypes::EMAIL->sanitizeCallback());
        
        $this->assertEquals("sanitize_textarea_field", SettingTypes::TEXTAREA->sanitizeCallback());
        
        $this->assertEquals("sanitize_url", SettingTypes::URL->sanitizeCallback());        
    }

    #[Test]
    public function test_sanitizeCallback_method_returns_null_for_non_sanitizable_types(): void {        
        $this->assertNull(SettingTypes::ARRAY->sanitizeCallback());
        $this->assertNull(SettingTypes::OBJECT->sanitizeCallback());   
    }

    #[Test]
    public function test_realValue_method_returns_correct_real_type_value_name(): void {        
        $this->assertEquals("string", SettingTypes::COLOR->realValue());
        $this->assertEquals("string", SettingTypes::EMAIL->realValue());
        $this->assertEquals("string", SettingTypes::TEXTAREA->realValue());
        $this->assertEquals("string", SettingTypes::URL->realValue());
        $this->assertEquals("string", SettingTypes::SELECT->realValue());

        $this->assertEquals("string", SettingTypes::STRING->realValue());

        $this->assertEquals("boolean", SettingTypes::BOOL->realValue());
        
        $this->assertEquals("integer", SettingTypes::INT->realValue());
        
        $this->assertEquals("number", SettingTypes::NUMBER->realValue());
        
        $this->assertEquals("array", SettingTypes::ARRAY->realValue());        

        $this->assertEquals("object", SettingTypes::OBJECT->realValue());     
    }

    #[Test]
    public function test_template_method_returns_correct_template_array_configuration(): void {        
        $this->assertEquals(['path' => 'partials/input', 'data' => ['type' => 'text']], SettingTypes::STRING->template());
        $this->assertEquals(['path' => 'partials/checkbox', 'data' => []], SettingTypes::BOOL->template());
        $this->assertEquals(['path' => 'partials/input', 'data' => ['type' => 'number', 'step' => '1']], SettingTypes::INT->template());
        $this->assertEquals(['path' => 'partials/input', 'data' => ['type' => 'number', 'step' => '.01']], SettingTypes::NUMBER->template());
        $this->assertEquals(['path' => 'partials/input', 'data' => ['type' => 'color']], SettingTypes::COLOR->template());
        $this->assertEquals(['path' => 'partials/input', 'data' => ['type' => 'email']], SettingTypes::EMAIL->template());
        $this->assertEquals(['path' => 'partials/textarea', 'data' => []], SettingTypes::TEXTAREA->template());
        $this->assertEquals(['path' => 'partials/input', 'data' => ['type' => 'url']], SettingTypes::URL->template());
        $this->assertEquals(['path' => 'partials/select', 'data' => []], SettingTypes::SELECT->template());
    }


    #[Test]
    public function test_template_method_returns_null_for_types_that_do_not_have_a_template(): void {        
        $this->assertNull(SettingTypes::ARRAY->template());
        $this->assertNull(SettingTypes::OBJECT->template());
    }


}
