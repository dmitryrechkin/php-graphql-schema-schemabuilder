<?php

declare(strict_types=1);

namespace DmitryRechkin\GraphQL\Schema\SchemaBuilder;

use DmitryRechkin\GraphQL\TypeRegistry\TypeRegistry;
use DmitryRechkin\GraphQL\TypeRegistry\TypeRegistryInterface;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use GraphQL\Utils\SchemaExtender;

class SchemaBuilder
{
	/**
	 * @var SchemaConfig
	 */
	private $schemaConfig;

	/**
	 * @var TypeRegistryInterface
	 */
	private $typeRegistry;

	/**
	 * @var DocumentNode
	 */
	private $documentNode;

	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->schemaConfig = new SchemaConfig();
		$this->typeRegistry = new TypeRegistry();
		$this->documentNode = new DocumentNode(['definitions' => []]);
	}

	/**
	 * sets schema config and returns itself
	 *
	 * @param SchemaConfig $schemaConfig
	 * @return self
	 */
	public function withSchemaConfig(SchemaConfig $schemaConfig): self
	{
		$this->schemaConfig = $schemaConfig;
		return $this;
	}

	/**
	 * sets type registry and returns itself
	 *
	 * @param TypeRegistryInterface $typeRegistry
	 * @return self
	 */
	public function withTypeRegistry(TypeRegistryInterface $typeRegistry): self
	{
		$this->typeRegistry = $typeRegistry;
		return $this;
	}

	/**
	 * sets document node and returns itself
	 *
	 * @param DocumentNode $documentNode
	 * @return self
	 */
	public function withDocumentNode(DocumentNode $documentNode): self
	{
		$this->documentNode = $documentNode;
		return $this;
	}

	/**
	 * build schema with the types from type registry and provided document node
	 *
	 * @return Schema
	 */
	public function build(): Schema
	{
		$schema = new Schema($this->schemaConfig->setTypes($this->getAllTypes()));

		return SchemaExtender::extend($schema, $this->documentNode);
	}

	/**
	 * returns all types we have
	 *
	 * @return array<string,Type>
	 */
	private function getAllTypes(): array
	{
		return array_merge($this->getSchemaConfigTypes(), $this->typeRegistry->getTypes());
	}

	/**
	 * returns schema config types
	 *
	 * @return array<string,Type>
	 */
	private function getSchemaConfigTypes(): array
	{
		$types = $this->schemaConfig->getTypes() ?? [];
		if (is_callable($types)) {
			$types = $types();
		}

		return $types;
	}
}
