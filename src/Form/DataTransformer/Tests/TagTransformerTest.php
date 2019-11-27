<?php
namespace App\Form\DataTransformer\Tests;

use App\Entity\Tag;
use App\Form\DataTransformer\TagTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;

class TagTransformerTest extends \PHPUnit\Framework\TestCase {
    
    public function testCreateTagsArrayFromString() {
        $transformer = $this->getMockedTransformer();
        $tags = $transformer->reverseTransform('Hello, Demo');
        $this->assertCount(2, $tags);
        $this->assertSame('Demo', $tags[1]->getName());

    }

    public function testUseAlreadyDefinedTag() {
        $tag = new Tag();
        $tag->setName('Chat');
        $transformer = $this->getMockedTransformer([$tag]);
        $tags = $transformer->reverseTransform('Chat, Chien');
        $this->assertCount(2, $tags);
        $this->assertSame($tag, $tags[0]);

    }

    public function testRemoveEmptyTags() {
        $tags = $this->getMockedTransformer()->reverseTransform('Hello,, Demo, , ,');
        $this->assertCount(2, $tags);
        $this->assertSame('Demo', $tags[1]->getName());
    }

    public function testRemoveDuplicateTags() {
        $tags = $this->getMockedTransformer()->reverseTransform('Demo,, Demo, ,Demo ,');
        $this->assertCount(1, $tags);
    }

    private function getMockedTransformer($result = []) {
        $tagRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tagRepository->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue($result));
        $entityManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($tagRepository));
        return new TagTransformer($entityManager);
    }
}