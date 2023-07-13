<?php

namespace Spin8\Tests\Unit;

use Closure;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\AdminNotice;
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

        $this->assertTrue($notice->type() === 'error');
    }

    #[Test]
    public function test_admin_notice_object_gets_instantiated_with_success_type(): void {
        $notice = AdminNotice::success('test');
        $this->assertInstanceOf(AdminNotice::class, $notice);

        $this->assertTrue($notice->type() === 'success');
    }

    #[Test]
    public function test_admin_notice_text_instantiated_gets_initialized(): void {
        $notice = AdminNotice::create('test');
        $this->assertTrue($notice->text() === 'test');

        $success = AdminNotice::success('test_success');
        $this->assertTrue($success->text() === 'test_success');

        $error = AdminNotice::error('test_error');
        $this->assertTrue($error->text() === 'test_error');
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
        
        //FIXME: fails because of a bug in WP_Mock. Pull request with fix already sent.
        WP_Mock::expectActionAdded('admin_notices', WP_Mock\Functions::type(Closure::class));        
        
        $notice->render();

        WP_Mock::assertHooksAdded();

    }
}
