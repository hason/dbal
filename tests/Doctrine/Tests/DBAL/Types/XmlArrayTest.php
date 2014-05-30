<?php

namespace Doctrine\Tests\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\Tests\DBAL\Mocks\MockPlatform;

require_once __DIR__ . '/../../TestInit.php';

class XmlArrayTest extends \Doctrine\Tests\DbalTestCase
{
    /**
     * @var \Doctrine\Tests\DBAL\Mocks\MockPlatform
     */
    protected $platform;

    /**
     * @var \Doctrine\DBAL\Types\XmlArrayType
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->platform = new MockPlatform();
        $this->type     = Type::getType('xml_array');
    }

    public function testReturnsBindingType()
    {
        $this->assertSame(\PDO::PARAM_STR, $this->type->getBindingType());
    }

    public function testReturnsName()
    {
        $this->assertSame(Type::XML_ARRAY, $this->type->getName());
    }

    public function testReturnsSQLDeclaration()
    {
        $this->assertSame('DUMMYCLOB', $this->type->getSQLDeclaration(array(), $this->platform));
    }

    public function testXmlNullConvertsToPHPValue()
    {
        $this->assertSame(array(), $this->type->convertToPHPValue(null, $this->platform));
    }

    public function testRequiresSQLCommentHint()
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }

    /**
     * @dataProvider getData
     */
    public function testConvertToDatabaseValue($data, $xml)
    {
        $this->assertSame($xml, $this->type->convertToDatabaseValue($data, $this->platform));
    }

    /**
     * @dataProvider getData
     */
    public function testConvertToPHPValue($data, $xml)
    {
        $this->assertSame($data, $this->type->convertToPHPValue($xml, $this->platform));
    }

    public function getData()
    {
        return array(
            array(array(0), "<?xml version=\"1.0\"?>\n<root><k:0>0</k:0></root>\n"),
            array(array(false), "<?xml version=\"1.0\"?>\n<root><k:0>false</k:0></root>\n"),
            array(array(null), "<?xml version=\"1.0\"?>\n<root><k:0>null</k:0></root>\n"),
        );
    }
}
