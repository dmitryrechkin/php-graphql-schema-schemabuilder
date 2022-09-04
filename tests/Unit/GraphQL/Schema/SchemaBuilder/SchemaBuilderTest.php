<?php

declare(strict_types=1);

namespace DmitryRechkin\Tests\Unit\GraphQL\TypeRegistry;

use DmitryRechkin\GraphQL\Schema\SchemaBuilder\SchemaBuilder;
use DmitryRechkin\GraphQL\TypeRegistry\TypeRegistry;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use PHPUnit\Framework\TestCase;

class SchemaBuilderTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testWithSchemaConfigReturnsSelf(): void
	{
		$schemaBuilder = new SchemaBuilder();
		$this->assertSame($schemaBuilder, $schemaBuilder->withSchemaConfig(new SchemaConfig()));
	}

	/**
	 * @return void
	 */
	public function testWithTypeRegistryReturnsSelf(): void
	{
		$schemaBuilder = new SchemaBuilder();
		$this->assertSame($schemaBuilder, $schemaBuilder->withTypeRegistry(new TypeRegistry()));
	}

	/**
	 * @return void
	 */
	public function testWithDocumentNodeReturnsSelf(): void
	{
		$schemaBuilder = new SchemaBuilder();
		$this->assertSame($schemaBuilder, $schemaBuilder->withDocumentNode(new DocumentNode([])));
	}

	/**
	 * @return void
	 */
	public function testBuildCallsSchemaSetTypesMethodFromTypeRegistryGetTypesMethod(): void
	{
		$typeRegistryMock = $this->createMock(TypeRegistry::class);
		$typeRegistryMock->expects($this->once())->method('getTypes')->willReturn([]);

		$schemaConfigMock = $this->createMock(SchemaConfig::class);
		$schemaConfigMock->expects($this->once())->method('setTypes')->willReturn($schemaConfigMock);

		$schemaBuilder = new SchemaBuilder();
		$schemaBuilder->withTypeRegistry($typeRegistryMock)->withSchemaConfig($schemaConfigMock);

		$schemaBuilder->build();
	}

	/**
	 * @return void
	 */
	public function testSchemaConfigTypesWorkWithCustomTypeFromTypeRegistry(): void
	{
		$source = <<<GRAPHQL
		type Query {
		  value: CustomType
		}
		GRAPHQL;

		$intType = new IntType();

		$schemaConfig = new SchemaConfig();
		$schemaConfig->setTypes([$intType->name => $intType]);

		$customType = new class () extends IntType {
			public $name = 'CustomType';
		};

		$schemaBuilder = new SchemaBuilder();
		$schemaBuilder->withSchemaConfig($schemaConfig);
		$schemaBuilder->withDocumentNode(Parser::parse($source));
		$schemaBuilder->withTypeRegistry((new TypeRegistry())->addType($customType));

		$schema = $schemaBuilder->build();

		$this->assertInstanceOf(Schema::class, $schema);
		$this->assertTrue($schema->hasType('CustomType'));
		$this->assertTrue($schema->hasType('Int'));
	}
}
