<?php

namespace platz1de\EasyEdit\selection;

use Closure;
use pocketmine\level\format\Chunk;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class DynamicBlockListSelection extends BlockListSelection
{
	/**
	 * @var Vector3
	 */
	private $point;

	/**
	 * DynamicBlockListSelection constructor.
	 * @param string  $player
	 * @param Vector3 $relativePlace
	 * @param int     $xSize
	 * @param int     $ySize
	 * @param int     $zSize
	 */
	public function __construct(string $player, Vector3 $relativePlace, int $xSize, int $ySize, int $zSize)
	{
		parent::__construct($player, "", new Vector3(), $xSize, $ySize, $zSize);
		$this->point = $relativePlace;
	}

	/**
	 * @param Position $place
	 * @return Chunk[]
	 */
	public function getNeededChunks(Position $place): array
	{
		return parent::getNeededChunks(Position::fromObject($place->subtract($this->getPoint()), $place->getLevel()));
	}

	/**
	 * @param Vector3 $place
	 * @param Closure $closure
	 * @return void
	 */
	public function useOnBlocks(Vector3 $place, Closure $closure): void
	{
		parent::useOnBlocks(new Vector3(), $closure);
	}

	/**
	 * @return Vector3
	 */
	public function getPoint(): Vector3
	{
		return $this->point;
	}

	/**
	 * @return string
	 */
	public function serialize(): string
	{
		return igbinary_serialize([
			"player" => $this->player,
			"chunks" => array_map(static function (Chunk $chunk) {
				return $chunk->fastSerialize();
			}, $this->getManager()->getChunks()),
			"level" => is_string($this->level) ? $this->level : $this->level->getName(),
			"minX" => $this->pos1->getX(),
			"minY" => $this->pos1->getY(),
			"minZ" => $this->pos1->getZ(),
			"maxX" => $this->pos2->getX(),
			"maxY" => $this->pos2->getY(),
			"maxZ" => $this->pos2->getZ(),
			"x" => $this->point->getX(),
			"y" => $this->point->getY(),
			"z" => $this->point->getZ(),
			"tiles" => $this->getTiles()
		]);
	}

	/**
	 * @param string $serialized
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function unserialize($serialized): void
	{
		$data = igbinary_unserialize($serialized);
		$this->point = new Vector3($data["x"], $data["y"], $data["z"]);
		parent::unserialize($serialized);
	}
}