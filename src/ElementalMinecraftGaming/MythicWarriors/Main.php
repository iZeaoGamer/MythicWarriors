<?php

namespace OofDevs\MythicWarriors;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use ElementalMinecraftGaming\MythicWarriors\XpInterval;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener {

    public $db;
    public $Interval;
    public $plugin;
    public $races;

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->db = new \SQLite3($this->getDataFolder() . "MythicWarriors.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS Charecter(user TEXT PRIMARY KEY, name TEXT, race TEXT, level INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS Titan(name TEXT PRIMARY KEY, race TEXT, size INT, damage INT, health INT, hunger INT, level INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS Race(race TEXT PRIMARY KEY, size INT,  damage INT, health INT, hunger INT, level INT, effect INT, ability TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS PlayerPower(user TEXT PRIMARY KEY, power INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS RacePower(race TEXT PRIMARY KEY, power INT);");
        $this->races = new Config($this->getDataFolder() . "races.yml", Config::YAML, array());
        $this->Interval = new Config($this->getDataFolder() . "Interval.yml", Config::YAML, array("Interval" => 30));
        $this->getScheduler()->scheduleRepeatingTask(new XpInterval($this), $this->Interval->get("Interval")*20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
       public function userRegistered($user) {
        $username = \SQLite3::escapeString($user);
        $search = $this->db->prepare("SELECT * FROM Charecter WHERE user = :user;");
        $search->bindValue(":user", $username);
        $start = $search->execute();
        $delta = $start->fetchArray(SQLITE3_ASSOC);
        return empty($delta) == false;
    }

    public function raceMade($race) {
        $racee = \SQLite3::escapeString($race);
        $search = $this->db->prepare("SELECT * FROM Race WHERE race = :race;");
        $search->bindValue(":race", $racee);
        $start = $search->execute();
        $delta = $start->fetchArray(SQLITE3_ASSOC);
        return empty($delta) == false;
    }

    public function getCname($user) {
        $search = $this->db->prepare("SELECT name FROM Charecter WHERE user = :user;");
        $search->bindValue(":user", $user);
        $start = $search->execute();
	$got = $start->fetchArray(SQLITE3_ASSOC);
        return $got["name"];
    }

    public function getLevel($user) {
        $search = $this->db->prepare("SELECT level FROM Charecter WHERE user = :user;");
        $search->bindValue(":user", $user);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["level"];
    }
    
    public function addLevel($user, $amount) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO Charecter (user, name, race, level) VALUES (:user, :name, :race, :level);");
        $del->bindValue(":user", $user);
        $del->bindValue(":name", $this->getCname($user));
        $del->bindValue(":race", $this->getRace($user));
        $del->bindValue(":level", $this->getlevel($user) + $amount);
        $start = $del->execute();
    }

    public function getPower($user) {
        $search = $this->db->prepare("SELECT power FROM PlayerPower WHERE user = :user;");
        $search->bindValue(":user", $user);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["power"];
    }

    public function getRace($user) {
        $search = $this->db->prepare("SELECT race FROM Charecter WHERE user = :user;");
        $search->bindValue(":user", $user);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return $da["race"];
    }
    
    public function raceMatch($user) {
        $level = $this->getLevel($user);
        $search = $this->db->prepare("SELECT race FROM Race WHERE level = :level;");
        $search->bindValue(":level", $level);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return $da["race"];
    }
    
    public function existRaceMatch($user) {
        $level = $this->getLevel($user);
        $lev = \SQLite3::escapeString($level);
        $search = $this->db->prepare("SELECT race FROM Race WHERE level = :level;");
        $search->bindValue(":level", $lev);
        $start = $search->execute();
        $delta = $start->fetchArray(SQLITE3_ASSOC);
        return empty($delta) == false;
    }
    
    public function getRaceHealth($race) {
        $search = $this->db->prepare("SELECT health FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["health"];
    }
    
    public function getRaceHunger($race) {
        $search = $this->db->prepare("SELECT hunger FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["hunger"];
    }
    
    public function getRaceDamage($race) {
        $search = $this->db->prepare("SELECT damage FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["damage"];
    }
    
    public function getRaceSize($race) {
        $search = $this->db->prepare("SELECT size FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["size"];
    }

    public function Charecter($user, $name, $race) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO Charecter (user, name, race) VALUES (:user, :name, :race);");
        $del->bindValue(":user", $user);
        $del->bindValue(":name", $name);
        $del->bindValue(":race", $race);
        $start = $del->execute();
        $dell = $this->db->prepare("INSERT OR REPLACE INTO PlayerPower (user) VALUES (:user);");
        $dell->bindValue(":user", $user);
        $start = $dell->execute();
    }
    
    public function setRace($user, $race) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO Charecter (user, name, race, level) VALUES (:user, :name, :race, :level);");
        $del->bindValue(":user", $user);
        $name = $this->getCname($user);
        $del->bindValue(":name", $name);
        $del->bindValue(":race", $race);
        $level = $this->getLevel($user);
        $del->bindValue(":level", $level);
        $start = $del->execute();
    }
    
    public function getRaceLevel($race) {
        $search = $this->db->prepare("SELECT level FROM Race WHERE race = :race;");
        $search->bindValue(":race", $race);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["level"];
    }

    public function addRace($race, $size, $damage, $health, $hunger, $level, $effect, $ability) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO race (race, size, damage, health, hunger, level, effect, ability) VALUES (:race, :size, :damage, :health, :hunger, :level, :effect, :ability);");
        $del->bindValue(":race", $race);
        $del->bindValue(":size", $size);
        $del->bindValue(":damage", $damage);
        $del->bindValue(":health", $health);
        $del->bindValue(":hunger", $hunger);
        $del->bindValue(":level", $level);
        $del->bindValue(":effect", $effect);
        $del->bindValue(":ability", $ability);
        $start = $del->execute();
        $dell = $this->db->prepare("INSERT OR REPLACE INTO RacePower (race) VALUES (:race);");
        $dell->bindValue(":race", $race);
        $start = $dell->execute();
    }
    
    public function checkXp() {
        $players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $player) {
            $xp = $player->getXpLevel();
            if ($xp >= 20) {
                $amount = 1;
                $this->addLevel($player, $amount);
                $player->setXpLevel($amount - 20);
            }
        }
    }
    
    public function join(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        if ($this->userRegistered($player) == true) {
            $prace = $this->getRace($player);
            $player->setFood($this->getRaceHunger($prace));
            $player->setMaxHealth($this->getRaceHealth($prace));
            $player->setHealth($this->getRaceHealth($prace));
            $player->setScale($this->getRaceSize($prace));
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (strtolower($command->getName()) == "createcharecter") {
            if ($sender->hasPermission("mythic.create")) {
                if ($sender instanceof Player) {
                    if (isset($args[0])) {
                        if (isset($args[1])) {
                            $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Creating chharecter sheet...");
                            $player = $sender->getName();
                            $user = strtolower($player);
                            $name = $args[0];
                            $race = $args[1];
                            $checkname = $this->userRegistered($user);
                            if ($checkname == false) {
                                $checkrace = $this->raceMade($race);
                                if ($checkrace == true) {
                                    $RaceLevel = $this->getRaceLevel($race);
                                    $level = $this->getLevel($user);
                                    if ($level >= $RaceLevel) {
                                        $prace = $race;
                                        $rsize = $this->getRaceSize($prace);
                                        $sender->setFood($this->getRaceHunger($prace));
                                        $sender->setMaxHealth($this->getRaceHealth($prace));
                                        $sender->setHealth($this->getRaceHealth($prace));
                                        $sender->setScale($this->getRaceSize($prace));
                                        $this->Charecter($user, $name, $race);
                                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Sheet created!");
                                        return true;
                                    } else {
                                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Requires level: $RaceLevel!");
                                    }
                                } else {
                                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "No such race!");
                                }
                            } else {
                                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Sheet already created!");
                            }
                        } else {
                            $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set race!");
                        }
                    } else {
                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set charecter name!");
                    }
                } else {
                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "In-Game only!");
                }
            } else {
                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "EasyAuth" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "No Permissions!");
                return false;
            }
        }

        if (strtolower($command->getName()) == "createrace") {
            if ($sender->hasPermission("mythic.createrace")) {
                if ($sender instanceof Player) {
                    if (isset($args[0])) {
                        if (isset($args[1])) {
                            if (isset($args[2])) {
                                if (isset($args[3])) {
                                    if (isset($args[4])) {
                                        if (isset($args[5])) {
                                            if (isset($args[6])) {
                                                if (isset($args[7])) {
                                                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Creating race...");
                                                    $player = $sender->getName();
                                                    $user = strtolower($player);
                                                    $race = $args[0];
                                                    $size = $args[1];
                                                    $damage = $args[2];
                                                    $health = $args[3];
                                                    $hunger = $args[4];
                                                    $level = $args[5];
                                                    $effect = $args[6];
                                                    $ability = $args[7];
                                                    $checkrace = $this->raceMade($race);
                                                    if ($checkrace == false) {
                                                        $this->addRace($race, $size, $damage, $health, $hunger, $level, $effect, $ability);
                                                        $this->races->set($race, [$level]);
                                                        $this->races->save();
                                                        $this->races->reload();
                                                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Sheet created!");
                                                        return true;
                                                    } else {
                                                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Race already created!");
                                                    }
                                                } else {
                                                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set the ability!");
                                                }
                                            } else {
                                                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set the races effect!");
                                            }
                                        } else {
                                            $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set the races level!");
                                        }
                                    } else {
                                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set the races hunger!");
                                    }
                                } else {
                                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set the races health!");
                                }
                            } else {
                                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set the races damage!");
                            }
                        } else {
                            $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set the races size!");
                        }
                    } else {
                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Please set the race name!");
                    }
                } else {
                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "In-Game only!");
                }
            } else {
                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "No Permissions!");
                return false;
            }
        }
        
         if (strtolower($command->getName()) == "addmlevel") {
            if ($sender->hasPermission("mystic.level")) {
                if ($sender instanceof Player) {
                    if (isset($args[0])) {
                        if (isset($args[1])) {
                            $player = $args[0];
                            $user = strtolower($player);
                            $amount = $args[1];
                            $checkname = $this->userRegistered($user);
                            if ($checkname == true) {
                                $this->addLevel($user, $amount);
                                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Added levels");
                                return true;
                            } else {
                                   $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "No player sheet"); 
                                }
                        } else {
                            $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Choose level");
                        }
                    } else {
                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Choose name");
                    }
                } else {
                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Must be in-game");
                }
            } else {
                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "No perms");
                return false;
            }
         }
         
         if (strtolower($command->getName()) == "mythicraces") {
            if ($sender->hasPermission("mythic.races")) {
                if ($sender instanceof Player) {
                    $use = $sender->getName();
                    $user = strtolower($use);
                    $plevel = $this->getLevel($user);
                    $sender->sendMessage(TextFormat::BLUE . "==Usable Races==");
                    $load = $this->db->prepare("SELECT race FROM Race WHERE level<=:level ORDER BY level DESC;");
                    $load->bindValue(":level", $plevel);
                    $start = $load->execute();
                    while ($check = $start->fetchArray(SQLITE3_ASSOC)) {
                        $race = $check['race'];
                        $level = $this->getRaceLevel($race);
                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "$race level $level");
                    }
                    return true;
                } else {
                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "In-Game only!");
                }
            } else {
                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "No Permissions!");
                return false;
            }
        }

        if (strtolower($command->getName()) == "csheet") {
            if ($sender->hasPermission("mythic.profile")) {
                if ($sender instanceof Player) {
                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Getting sheet...");
                    $player = $sender->getName();
                    $user = strtolower($player);
                    $checkrace = $this->userRegistered($user);
                    if ($checkrace == true) {
                        $name = $this->getCname($user);
                        $race = $this->getRace($user);
                        $size = $this->getRaceSize($race);
                        $level = $this->getLevel($user);
                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "===Charecter Sheet===\nCharecter: $name\nRace; $race\nSize: $size\nLevel: $level");
                        return true;
                    } else {
                        $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "Not charecter sheet found!");
                    }
                } else {
                    $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "In-Game only!");
                }
            } else {
                $sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . "[MythicWarriors]" . TextFormat::YELLOW . "] " . TextFormat::GREEN . "No Permissions!");
                return false;
            }
        }
        return false;
    }
}
