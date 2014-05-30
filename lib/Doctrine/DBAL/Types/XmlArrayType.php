<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Array Type which can be used to generate simple xml trees.
 *
 * @since  2.5
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class XmlArrayType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $self = $this;
        $mapper = function($data, \SimpleXMLElement $node) use (&$mapper, $self) {
            foreach ((array) $data as $key => $value) {
                if (is_array($value)) {
                    $mapper($value, $node->addChild($key));
                } else {
                    $node->addChild($key, $self->phpToXml($value));
                }
            }
        };

        $root = new \SimpleXMLElement('<root/>');
        $mapper($value, $root);

        return $root->asXML();
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return array();
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        $data = array();
        $xml = simplexml_load_string($value);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Type::XML_ARRAY;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * Converts php types to xml types.
     *
     * @param mixed $value Value to convert
     *
     * @return string
     *
     * @throws RuntimeException When trying to dump object or resource
     */
    public static function phpToXml($value)
    {
        switch (true) {
            case null === $value:
                return 'null';
            case true === $value:
                return 'true';
            case false === $value:
                return 'false';
            case is_object($value) || is_resource($value):
                throw new \InvalidArgumentException('Unable to convert an object or a resource to xml.');
            default:
                return (string) $value;
        }
    }

    /**
     * Converts an xml value to a PHP type.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function xmlToPhp($value)
    {
        /*
         * This method is copied from code of the Symfony Framework (https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Config/Util/XmlUtils.php),
         * which is released under the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
         * Copyright (c) 2004-2014 Fabien Potencier <fabien@symfony.com>
         */

        $value = (string) $value;
        $lowercaseValue = strtolower($value);

        switch (true) {
            case 'null' === $lowercaseValue:
                return null;
            case ctype_digit($value):
                $raw = $value;
                $cast = intval($value);

                return '0' == $value[0] ? octdec($value) : (((string) $raw == (string) $cast) ? $cast : $raw);
            case isset($value[1]) && '-' === $value[0] && ctype_digit(substr($value, 1)):
                $raw = $value;
                $cast = intval($value);

                return '0' == $value[1] ? octdec($value) : (((string) $raw == (string) $cast) ? $cast : $raw);
            case 'true' === $lowercaseValue:
                return true;
            case 'false' === $lowercaseValue:
                return false;
            case isset($value[1]) && '0b' == $value[0].$value[1]:
                return bindec($value);
            case is_numeric($value):
                return '0x' == $value[0].$value[1] ? hexdec($value) : floatval($value);
            case preg_match('/^(-|\+)?[0-9]+(\.[0-9]+)?$/', $value):
                return floatval($value);
            default:
                return $value;
        }
    }
}
