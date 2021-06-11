<?php

namespace platz1de\EasyEdit\pattern\logic\relation;

use platz1de\EasyEdit\pattern\ParseError;
use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\Selection;
use pocketmine\level\utils\SubChunkIteratorManager;

class BlockPattern extends Pattern
{
	/**
	 * @param int                     $x
	 * @param int                     $y
	 * @param int                     $z
	 * @param SubChunkIteratorManager $iterator
	 * @param Selection               $selection
	 * @return bool
	 */
	public function isValidAt(int $x, int $y, int $z, SubChunkIteratorManager $iterator, Selection $selection): bool
	{
		$iterator->moveTo($x, $y, $z);
		return ($iterator->currentSubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f) === $this->args[0]->getId()) && ($this->args[0]->getDamage() === -1 || $iterator->currentSubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f) === $this->args[0]->getDamage());
	}

	public function check(): void
	{
		try {
			$this->args[0] = Pattern::getBlockType($this->args[0] ?? "");
		} catch (ParseError $error) {
			throw new ParseError("Block needs a block as first Argument, " . ($this->args[0] ?? "") . " given");
		}
	}
}