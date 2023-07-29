<?php

namespace Spin8\Tests\Unit\WP;

use Closure;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\WP\AdminNotice;
use Spin8\Tests\TestCase;
use WP_Mock;

#[CoversClass(AdminNotice::class)]
final class AdminNoticeTest extends TestCase {

    #[Test]
    public function test_admin_notice_object_gets_instantiated(): void {
        $this->assertInstanceOf(AdminNotice::class, AdminNotice::create('test'));
    }

    #[Test]
    public function test_admin_notice_object_gets_instantiated_with_error_type(): void {
        $notice = AdminNotice::error('test');
        $this->assertInstanceOf(AdminNotice::class, $notice);

        $this->assertSame('error', $notice->type());
    }

    #[Test]
    public function test_admin_notice_object_gets_instantiated_with_success_type(): void {
        $notice = AdminNotice::success('test');
        $this->assertInstanceOf(AdminNotice::class, $notice);

        $this->assertSame('success', $notice->type());
    }

    #[Test]
    public function test_admin_notice_text_instantiated_gets_initialized(): void {
        $notice = AdminNotice::create('test');
        $this->assertSame('test', $notice->text());

        $success = AdminNotice::success('test_success');
        $this->assertSame('test_success', $success->text());

        $error = AdminNotice::error('test_error');
        $this->assertSame('test_error', $error->text());
    }

    #[Test]
    public function test_admin_notice_can_be_set_as_dismissible_by_set_dismissible_method(): void {
        $notice = AdminNotice::create('test');
        $this->assertFalse($notice->isDismissible());

        $notice->setDismissible();
        $this->assertTrue($notice->isDismissible());

        $notice->setDismissible(false);
        $this->assertFalse($notice->isDismissible());
    }

    #[Test]
    public function test_admin_notice_gets_rendered_by_render_method(): void { 
        
        $notice = AdminNotice::create('test');                
        
        //HACK: uses a custom version of WP_Mock to successfully run.
        WP_Mock::expectActionAdded('admin_notices', WP_Mock\Functions::type(Closure::class));        
        
        $notice->render();

        WP_Mock::assertHooksAdded();

    }
}
