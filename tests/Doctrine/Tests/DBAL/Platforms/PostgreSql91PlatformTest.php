<?php

namespace Doctrine\Tests\DBAL\Platforms;

use Doctrine\DBAL\Platforms\PostgreSql91Platform;
use Doctrine\DBAL\Types\Type;

class PostgreSql91PlatformTest extends PostgreSqlPlatformTest
{
    public function createPlatform()
    {
        return new PostgreSql91Platform();
    }

    public function testColumnCollationDeclarationSQL()
    {
        $this->assertEquals(
            'COLLATE "en_US.UTF-8"',
            $this->_platform->getColumnCollationDeclarationSQL('en_US.UTF-8')
        );
    }
}
