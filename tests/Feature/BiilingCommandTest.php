<?php

namespace Devinweb\LaravelPaytabs\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class BillingCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function a_new_billing_class_is_created()
    {

        // destination path of the Foo class
        $billingClass = app_path('Billing/MyBillingClass.php');

        // make sure we're starting from a clean state
        if (File::exists($billingClass)) {
            unlink($billingClass);
        }
        $this->assertFalse(File::exists($billingClass));

        Artisan::call('make:billing MyBillingClass');

        $this->assertTrue(File::exists($billingClass));

        $expectedContents = <<<CLASS
<?php

namespace App\Billing;

use Devinweb\LaravelPaytabs\Contracts\BillingInterface;

class MyBillingClass implements BillingInterface
{
    /**
     * Get the billing data.
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            //
        ];
    }
}
CLASS;
        $this->assertEquals($expectedContents, file_get_contents($billingClass));
    }
}
