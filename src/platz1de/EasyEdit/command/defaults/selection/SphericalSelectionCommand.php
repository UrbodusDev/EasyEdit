<?php

namespace platz1de\EasyEdit\command\defaults\selection;

use platz1de\EasyEdit\command\flags\CommandFlagCollection;
use platz1de\EasyEdit\command\flags\FloatCommandFlag;
use platz1de\EasyEdit\command\SimpleFlagArgumentCommand;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\Sphere;
use platz1de\EasyEdit\session\Session;

abstract class SphericalSelectionCommand extends SimpleFlagArgumentCommand
{
	public function __construct(string $name, array $permissions, array $aliases = [])
	{
		parent::__construct($name, ["radius" => false], $permissions, $aliases);
	}

	public function getKnownFlags(Session $session): array
	{
		return [
			"radius" => new FloatCommandFlag("radius", [], "r")
		];
	}

	/**
	 * @param Session               $session
	 * @param CommandFlagCollection $flags
	 */
	public function process(Session $session, CommandFlagCollection $flags): void
	{
		if ($flags->hasFlag("radius")) {
			$this->processSelection($session, Sphere::aroundPoint($session->asPlayer()->getWorld()->getFolderName(), $session->asPlayer()->getPosition(), $flags->getFloatFlag("radius")));
		} else {
			$this->processSelection($session, $session->getSelection());
		}
	}

	abstract public function processSelection(Session $session, Selection $selection): void;
}