<?php

namespace platz1de\EasyEdit\task\selection;

use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\BlockListSelection;
use platz1de\EasyEdit\selection\DynamicBlockListSelection;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\StaticBlockListSelection;
use platz1de\EasyEdit\task\EditTask;
use platz1de\EasyEdit\utils\TileUtils;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\utils\SubChunkIteratorManager;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\tile\Tile;

class PasteTask extends EditTask
{
	/**
	 * PasteTask constructor.
	 * @param BlockListSelection $selection
	 * @param Position           $place
	 */
	public function __construct(BlockListSelection $selection, Position $place)
	{
		parent::__construct($selection, new Pattern([], []), $place);
	}

	/**
	 * @return string
	 */
	public function getTaskName(): string
	{
		return "paste";
	}

	/**
	 * @param SubChunkIteratorManager $iterator
	 * @param CompoundTag[]           $tiles
	 * @param Selection               $selection
	 * @param Pattern                 $pattern
	 * @param Vector3                 $place
	 * @param BlockListSelection      $toUndo
	 * @param SubChunkIteratorManager $origin
	 * @param int                     $changed
	 */
	public function execute(SubChunkIteratorManager $iterator, array &$tiles, Selection $selection, Pattern $pattern, Vector3 $place, BlockListSelection $toUndo, SubChunkIteratorManager $origin, int &$changed): void
	{
		/** @var DynamicBlockListSelection $selection */
		Selection::validate($selection, DynamicBlockListSelection::class);
		$place = $place->subtract($selection->getPoint());
		$selection->useOnBlocks($place, function (int $x, int $y, int $z) use ($iterator, &$tiles, $selection, $place, $toUndo, &$changed): void {
			$selection->getIterator()->moveTo($x, $y, $z);
			$blockId = $selection->getIterator()->currentSubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f);
			if (Selection::processBlock($blockId)) {
				$iterator->moveTo($x + $place->getX(), $y + $place->getY(), $z + $place->getZ());
				$toUndo->addBlock($x + $place->getX(), $y + $place->getY(), $z + $place->getZ(), $iterator->currentSubChunk->getBlockId($x + $place->getX() & 0x0f, $y + $place->getY() & 0x0f, $z + $place->getZ() & 0x0f), $iterator->currentSubChunk->getBlockData($x + $place->getX() & 0x0f, $y + $place->getY() & 0x0f, $z + $place->getZ() & 0x0f));
				$iterator->currentSubChunk->setBlock(($x + $place->getX()) & 0x0f, ($y + $place->getY()) & 0x0f, ($z + $place->getZ()) & 0x0f, $blockId, $selection->getIterator()->currentSubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f));
				$changed++;

				if (isset($tiles[Level::blockHash($x + $place->getX(), $y + $place->getY(), $z + $place->getZ())])) {
					$toUndo->addTile($tiles[Level::blockHash($x + $place->getX(), $y + $place->getY(), $z + $place->getZ())]);
					unset($tiles[Level::blockHash($x + $place->getX(), $y + $place->getY(), $z + $place->getZ())]);
				}
			}
		});

		foreach ($selection->getTiles() as $tile) {
			$compoundTag = TileUtils::offsetCompound($tile, $place);
			$tiles[Level::blockHash($compoundTag->getInt(Tile::TAG_X), $compoundTag->getInt(Tile::TAG_Y), $compoundTag->getInt(Tile::TAG_Z))] = $compoundTag;
		}
	}

	/**
	 * @param Selection $selection
	 * @param Vector3   $place
	 * @param string    $level
	 * @return StaticBlockListSelection
	 */
	public function getUndoBlockList(Selection $selection, Vector3 $place, string $level): BlockListSelection
	{
		/** @var DynamicBlockListSelection $selection */
		Selection::validate($selection, DynamicBlockListSelection::class);
		return new StaticBlockListSelection($selection->getPlayer(), $level, $place->subtract($selection->getPoint()), $selection->getPos2()->getX() - $selection->getPos1()->getX(), $selection->getPos2()->getY() - $selection->getPos1()->getY(), $selection->getPos2()->getZ() - $selection->getPos1()->getZ());
	}

	/**
	 * @param Selection $selection
	 * @param float     $time
	 * @param int       $changed
	 */
	protected function notifyUser(Selection $selection, float $time, int $changed): void
	{
		Messages::send($selection->getPlayer(), "blocks-pasted", ["{time}" => $time, "{changed}" => $changed]);
	}
}