<?php

namespace EasyPost\Test;

use EasyPost\EasyPostClient;
use EasyPost\Exception\Api\ApiException;

class ErrorTest extends \PHPUnit\Framework\TestCase
{
    private static $client;

    /**
     * Setup the testing environment for this file.
     */
    public static function setUpBeforeClass(): void
    {
        TestUtil::setupVcrTests();
        self::$client = new EasyPostClient(getenv('EASYPOST_TEST_API_KEY'));
    }

    /**
     * Cleanup the testing environment once finished.
     */
    public static function tearDownAfterClass(): void
    {
        TestUtil::teardownVcrTests();
    }

    /**
     * Test creating a bad shipment and retrieving errors.
     */
    public function testError()
    {
        TestUtil::setupCassette('errors/errors.yml');

        // Create a bad shipment so we can work with errors
        try {
            self::$client->shipment->create();
        } catch (ApiException $error) {
            $this->assertEquals(422, $error->getHttpStatus());
            $this->assertEquals('PARAMETER.REQUIRED', $error->code);
            $this->assertEquals('Missing required parameter.', $error->getMessage());
            $this->assertEquals(['field' => 'shipment', 'message' => 'cannot be blank'], $error->errors[0]);
            $this->assertEquals('{"error":{"code":"PARAMETER.REQUIRED","message":"Missing required parameter.","errors":[{"field":"shipment","message":"cannot be blank"}]}}', $error->getHttpBody()); // phpcs:ignore

            // We check that the pretty printed output is the same here, leave the odd formatting as
            // it is here and do not auto-format the next few lines.
            $error->prettyPrint();
            $this->expectOutputString('PARAMETER.REQUIRED (422): Missing required parameter.
Field errors:
  field: shipment
  message: cannot be blank

');
        }
    }
}
