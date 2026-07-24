<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Tests\Stubs\TestHttpConnection;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class HasHttpVerbsTest extends TestCase
{
    public function test_map_http_verb_normalizes_supported_verbs()
    {
        $connection = new TestHttpConnection();

        $this->assertSame('GET', $connection->callMapHttpVerb('get'));
        $this->assertSame('GET', $connection->callMapHttpVerb('GET'));
        $this->assertSame('POST', $connection->callMapHttpVerb('post'));
        $this->assertSame('POST', $connection->callMapHttpVerb('POST'));
        $this->assertSame('PUT', $connection->callMapHttpVerb('put'));
        $this->assertSame('PUT', $connection->callMapHttpVerb('PUT'));
    }

    public static function invalidVerbProvider(): array
    {
        return [
            'delete lowercase'   => ['delete'],
            'DELETE uppercase'   => ['DELETE'],
            'patch'              => ['patch'],
            'empty string'       => [''],
            'mixed case Get'     => ['Get'],
        ];
    }

    #[DataProvider('invalidVerbProvider')]
    public function test_map_http_verb_throws_for_unsupported_verbs(string $verb)
    {
        // 'Get' (mixed case) is included deliberately: the match() arms only list
        // 'get'/'GET', so any other casing is NOT matched and throws too.
        $connection = new TestHttpConnection();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid HTTP verb: {$verb}");

        $connection->callMapHttpVerb($verb);
    }

    public function test_set_http_verb_sets_normalized_method_and_is_fluent()
    {
        $connection = new TestHttpConnection();

        $result = $connection->callSetHttpVerb('post');

        $this->assertSame('POST', $connection->getMethod());
        $this->assertSame($connection, $result);
    }

    public function test_set_http_verb_with_invalid_verb_throws_and_leaves_method_unset()
    {
        $connection = new TestHttpConnection();

        try {
            $connection->callSetHttpVerb('delete');
            $this->fail('Expected exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertSame('Invalid HTTP verb: delete', $e->getMessage());
        }

        // CHARACTERIZATION: setHttpVerb() throwing means $method was never assigned;
        // it's a typed property with no default, so reading it now throws \Error.
        $this->expectException(\Error::class);

        $connection->getMethod();
    }
}
