<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use DateTime;
use InvalidArgumentException;
use SlopeIt\ClockMock\ClockMock;
use Yiisoft\Db\Query\Query;
use Yiisoft\Rbac\Db\Exception\SeparatorCollisionException;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\Tests\Common\ItemsStorageTestTrait;

abstract class ItemsStorageTest extends TestCase
{
    use ItemsStorageTestTrait {
        setUp as protected traitSetUp;
        tearDown as protected traitTearDown;
        testClear as protected traitTestClear;
        testRemove as protected traitTestRemove;
        testClearPermissions as protected traitTestClearPermissions;
        testClearRoles as protected traitTestClearRoles;
    }

    protected static array $migrationsSubfolders = ['items'];

    protected function setUp(): void
    {
        if ($this->name() === 'testGetAccessTreeWithCustomSeparator') {
            ClockMock::freeze(new DateTime('2023-12-24 17:51:18'));
        }

        parent::setUp();
        $this->traitSetUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->traitTearDown();

        if ($this->name() === 'testGetAccessTreeWithCustomSeparator') {
            ClockMock::reset();
        }
    }

    public function testClear(): void
    {
        $this->traitTestClear();

        $itemsChildrenExist = (new Query($this->getDatabase()))
            ->from(self::$itemsChildrenTable)
            ->exists();
        $this->assertFalse($itemsChildrenExist);
    }

    public function testRemove(): void
    {
        $storage = $this->getItemsStorage();
        $initialItemChildrenCount = count($storage->getAllChildren('Parent 2'));

        $this->traitTestRemove();

        $itemsChildren = (new Query($this->getDatabase()))
            ->from(self::$itemsChildrenTable)
            ->count();
        $this->assertSame($this->initialItemsChildrenCount - $initialItemChildrenCount, $itemsChildren);
    }

    public function testClearPermissions(): void
    {
        $this->traitTestClearPermissions();

        $itemsChildrenCount = (new Query($this->getDatabase()))
            ->from(self::$itemsChildrenTable)
            ->count();
        $this->assertSame($this->initialBothRolesChildrenCount, $itemsChildrenCount);
    }

    public function testClearRoles(): void
    {
        $this->traitTestClearRoles();

        $itemsChildrenCount = (new Query($this->getDatabase()))
            ->from(self::$itemsChildrenTable)
            ->count();
        $this->assertSame($this->initialBothPermissionsChildrenCount, $itemsChildrenCount);
    }

    public function testGetAccessTreeSeparatorCollision(): void
    {
        $this->expectException(SeparatorCollisionException::class);
        $this->expectExceptionMessage('Separator collision has been detected.');
        $this->getItemsStorage()->getAccessTree('posts.view');
    }

    public function testGetAccessTreeWithCustomSeparator(): void
    {
        $createdAt = (new DateTime('2023-12-24 17:51:18'))->getTimestamp();
        $postsViewPermission = (new Permission('posts.view'))->withCreatedAt($createdAt)->withUpdatedAt($createdAt);
        $postsViewerRole = (new Role('posts.viewer'))->withCreatedAt($createdAt)->withUpdatedAt($createdAt);
        $postsRedactorRole = (new Role('posts.redactor'))->withCreatedAt($createdAt)->withUpdatedAt($createdAt);
        $postsAdminRole = (new Role('posts.admin'))->withCreatedAt($createdAt)->withUpdatedAt($createdAt);

        $this->assertEquals(
            [
                'posts.view' => ['item' => $postsViewPermission, 'children' => []],
                'posts.viewer' => ['item' => $postsViewerRole, 'children' => ['posts.view' => $postsViewPermission]],
                'posts.redactor' => [
                    'item' => $postsRedactorRole,
                    'children' => ['posts.view' => $postsViewPermission, 'posts.viewer' => $postsViewerRole],
                ],
                'posts.admin' => [
                    'item' => $postsAdminRole,
                    'children' => [
                        'posts.view' => $postsViewPermission,
                        'posts.viewer' => $postsViewerRole,
                        'posts.redactor' => $postsRedactorRole,
                    ],
                ],
            ],
            $this->getItemsStorage()->getAccessTree('posts.view')
        );
    }

    public static function dataInvalidConfiguration(): array
    {
        $exceptionMessage = 'Names separator must be exactly 1 character long.';

        return [
            [['namesSeparator' => ',,'], $exceptionMessage],
            [['namesSeparator' => ''], $exceptionMessage],
            [['namesSeparator' => ' ,'], $exceptionMessage],
            [['namesSeparator' => ', '], $exceptionMessage],
            [['namesSeparator' => ' , '], $exceptionMessage],
        ];
    }

    /**
     * @dataProvider dataInvalidConfiguration
     */
    public function testInvalidConfiguration(array $arguments, string $exceptionMessage): void
    {
        $arguments = array_merge(['database' => $this->getDatabase()], $arguments);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);
        new ItemsStorage(...$arguments);
    }

    protected function populateItemsStorage(): void
    {
        $fixtures = $this->getFixtures();

        $this
            ->getDatabase()
            ->createCommand()
            ->batchInsert(self::$itemsTable, ['name', 'type', 'createdAt', 'updatedAt'], $fixtures['items'])
            ->execute();
        $this
            ->getDatabase()
            ->createCommand()
            ->batchInsert(self::$itemsChildrenTable, ['parent', 'child'], $fixtures['itemsChildren'])
            ->execute();
    }

    protected function populateDatabase(): void
    {
        // Skip
    }

    protected function getItemsStorage(): ItemsStorageInterface
    {
        return match ($this->name()) {
            'testGetAccessTreeSeparatorCollision' => new ItemsStorage($this->getDatabase(), namesSeparator: '.'),
            'testGetAccessTreeWithCustomSeparator' => new ItemsStorage($this->getDatabase(), namesSeparator: '|'),
            default => new ItemsStorage($this->getDatabase()),
        };
    }
}
