<?php

namespace Tourze\LarkOAuth2LoginBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State;

#[When(env: 'test')]
class LarkOAuth2StateFixtures extends Fixture implements DependentFixtureInterface
{
    public const LARK_STATE_VALID_REFERENCE = 'lark-state-valid';
    public const LARK_STATE_USED_REFERENCE = 'lark-state-used';

    public function load(ObjectManager $manager): void
    {
        $config = $this->getReference(LarkOAuth2ConfigFixtures::LARK_CONFIG_REFERENCE, LarkOAuth2Config::class);

        $validState = new LarkOAuth2State();
        $validState->setState(md5('test_state_' . time()));
        $validState->setConfig($config);
        $validState->setSessionId('test_session_id_123');

        $manager->persist($validState);
        $this->addReference(self::LARK_STATE_VALID_REFERENCE, $validState);

        $usedState = new LarkOAuth2State();
        $usedState->setState(md5('used_state_' . time()));
        $usedState->setConfig($config);
        $usedState->setSessionId('test_session_id_456');
        $usedState->markAsUsed();

        $manager->persist($usedState);
        $this->addReference(self::LARK_STATE_USED_REFERENCE, $usedState);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LarkOAuth2ConfigFixtures::class,
        ];
    }
}
