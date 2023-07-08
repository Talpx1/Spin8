<?php

namespace JsonLDForWP\Tests\Framework\Unit;

use JsonLDForWP\Framework\AdminNotice;
use Mockery;
use Tests\TestCase;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;

/**
 * @coversDefaultClass \JsonLDForWP\Framework\AdminNotice
 */
class AdminNoticeTest extends TestCase {

    /** 
     * @test  
     * @covers ::create
     */
    public function test_admin_notice_object_gets_instantiated() {
        $this->assertInstanceOf(AdminNotice::class, AdminNotice::create('test'));
    }

    /** 
     * @test  
     * @covers ::error
     * @covers ::setType
     * @covers ::type
     */
    public function test_admin_notice_object_gets_instantiated_with_error_type() {
        $notice = AdminNotice::error('test');
        $this->assertInstanceOf(AdminNotice::class, $notice);

        $this->assertTrue($notice->type() === 'error');
    }

    /** 
     * @test  
     * @covers ::success
     * @covers ::setType
     * @covers ::type
     */
    public function test_admin_notice_object_gets_instantiated_with_success_type() {
        $notice = AdminNotice::success('test');
        $this->assertInstanceOf(AdminNotice::class, $notice);

        $this->assertTrue($notice->type() === 'success');
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::text
     */
    public function test_admin_notice_text_instantiated_gets_initialized() {
        $notice = AdminNotice::create('test');
        $this->assertTrue($notice->text() === 'test');

        $success = AdminNotice::success('test_success');
        $this->assertTrue($success->text() === 'test_success');

        $error = AdminNotice::error('test_error');
        $this->assertTrue($error->text() === 'test_error');
    }

    /** 
     * @test  
     * @covers ::setDismissible
     * @covers ::isDismissible
     */
    public function test_admin_notice_can_be_set_as_dismissible_by_set_dismissible_method() {
        $notice = AdminNotice::create('test');
        $this->assertFalse($notice->isDismissible());

        $notice->setDismissible();
        $this->assertTrue($notice->isDismissible());

        $notice->setDismissible(false);
        $this->assertFalse($notice->isDismissible());
    }

    /** 
     * @test  
     * @covers ::render
     */
    public function test_admin_notice_gets_rendered_by_render_method() {
        $notice = AdminNotice::create('test');
        expectAdded('admin_notices')->once()->with(Mockery::type('Closure'));
        $notice->render();
    }
}
