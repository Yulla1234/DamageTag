<?php

/**
 * @name DamageTag
 * @author YL
 * @main DamageTag\DamageTag
 * @version 1.0.0
 * @api [4.0.0, 3.9.0]
 */

namespace DamageTag;

use pocketmine\{
  plugin\PluginBase,
  event\Listener,
  event\entity\EntityDamageEvent,
  event\entity\EntityRegainHealthEvent,
  Player,
  level\particle\FloatingTextParticle,
  level\Level,
  math\Vector3,
  scheduler\Task
};

class DamageTag extends PluginBase implements Listener {

  public function onEnable () {
    $this->getServer()->getPluginManager()->registerEvents ($this, $this);
  }

  public function DamageInfo (Player $player, $damage, $color) {
    $vec = new Vector3 ($player->x, $player->y + 1, $player->z);
    $particle = new FloatingTextParticle ($vec, "§l§{$color}[§f" . $damage . "§{$color}]");
    $player->level->addParticle ($particle);
    $this->getScheduler()->scheduleDelayedTask (new class ($player->level, $particle) extends Task {

      public function __construct ($level, $particle) {
        $this->level = $level;
        $this->particle = $particle;
      }

      public function onRun (int $currentTick) : void {
        $this->particle->setInvisible (true);
        $this->level->addParticle ($this->particle);
      }
    }, 20 * 3);
  }

  public function DamageEvent (EntityDamageEvent $e) {
    $entity = $e->getEntity();
    if ($entity instanceof Player) {
      if (!$e->isCancelled()) {
        $this->DamageInfo ($entity, $e->getFinalDamage(), "c");
      }
      return true;
    }
  }

  public function RegainHealthEvent (EntityRegainHealthEvent $e) {
    $entity = $e->getEntity();
    if ($entity instanceof Player) {
      if (!$e->isCancelled()) {
        $this->DamageInfo ($entity, $e->getAmount(), "a");
      }
      return true;
    }
  }
}
