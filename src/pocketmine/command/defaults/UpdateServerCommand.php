<?php
namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;

class UpdateServerCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.updateserver.description",
			"%pocketmine.command.updateserver.usage",
			["serverupdate"]
		);
		$this->setPermission("pocketmine.command.updateserver");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		$branch = 'master';
		$raw = json_decode(Utils::getURL('https://circleci.com/api/v1/project/NycuRO/Apollo/tree/'.$branch.'?circle-token:token&limit=1&offset=1&filter=successfull'), true);
		$buildinfo = $raw[0];
		if(file_exists('Apollo#'.$buildinfo['build_num'].'.phar')){
			$sender->sendMessage(TF::Red.'Server is already up to date!');
		}else{
		    foreach(glob("Apollo*.phar") as $file){
			    unlink($file);
		    }
			$rawartifactdata = json_decode(Utils::getURL('https://circleci.com/api/v1/project/NycuRO/Apollo/'.$buildinfo['build_num'].'/artifacts?circle-token=:token&branch=:branch&filter=:filter', true));
			$artifactdata = get_object_vars($rawartifactdata[0]);
		    file_put_contents('Apollo#'.$buildinfo['build_num'].'.phar', Utils::getURL($artifactdata['url']));
			if(file_exists('Apollo#'.$buildinfo['build_num'].'.phar') && filesize('Apollo#'.$buildinfo['build_num'].'.phar') > 0){
			    $sender->sendMessage(TF::GREEN.'Successfully downloaded Apollo#'.$buildinfo['build_num'].'.phar!');
				$sender->sendMessage(TF::RED.'Server restart needed');
				sleep(3);
				Server::forceShutdown();
			}else{
				$sender->sendMessage(TF::RED.'Could not update the server');
			}
	    }
	}
}