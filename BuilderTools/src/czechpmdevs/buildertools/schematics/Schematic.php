<?php

/**
 * Copyright 2018 CzechPMDevs
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace czechpmdevs\buildertools\schematics;

use czechpmdevs\buildertools\editors\object\BlockList;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;

/**
 * Class Schematic
 * @package czechpmdevs\buildertools\schematics
 */
class Schematic {

    /** @var string $file */
    protected $file;

    /** @var CompoundTag $data */
    protected $data;

    /** @var BlockList $blockList */
    protected $blockList;

    /**
     * @var int $width
     *
     * Size along the x axis
     */
    protected $width;

    /**
     * @var int $height
     *
     * Size along the y axis
     */
    protected $height;

    /**
     * @var int $length
     *
     * Size along the z axis
     */
    protected $length;

    /**
     * @var string $materials
     *
     * Classic -> MC:JAVA world format
     * Pocket -> MC:BEDROCK world format
     * Alpha -> MC:ALPHA world format - is not supported
     */
    protected $materials = "Classic";


    /**
     * Schematic constructor.
     * @param string $file
     */
    public function __construct(string $file) {
        $this->file = $file;
        $nbt = new BigEndianNBTStream();
        $this->data = $nbt->readCompressed(file_get_contents($file));
        $this->width = $this->data->getShort("Width");
        $this->height = $this->data->getShort("Height");
        $this->length = $this->data->getShort("Length");

        if($this->data->offsetExists("Materials")) {
            $this->materials = $this->data->getString("Materials");
        }

        $this->blockList = new BlockList();


        $centerTag = $this->data->getCompoundTag("Metadata");
        $center = new Vector3($centerTag->getInt("WEOffsetX"), $centerTag->getInt("WEOffsetY"), $centerTag->getInt("WEOffsetZ"));
        $this->blockList->setPlayerPosition($center);


        /** @var string $blocks */
        $blocks = $this->data->getByteArray("BlockData");

        $i = 0;
        for($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                for($z = 0; $z < $this->length; $z++) {
                    $id = ord($blocks{$i});
                    $damage = ord($blocks{$i});
                    if($damage >= 16) $damage = 0; // prevents bug
                    $this->blockList->addBlock(new Vector3($x, $y, $z), Block::get($id, $damage));
                    $i++;
                }
            }
        }

        if($this->materials == "Classic") {
            $this->materials = "Pocket";

        }
    }

    /**
     * @return BlockList
     */
    public function getBlockList(): BlockList {
        return $this->blockList;
    }

    /**
     * @return CompoundTag
     */
    public function getCompoundTag(): CompoundTag {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getXAxis(): int {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getYAxis(): int {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getZAxis(): int {
        return $this->getZAxis();
    }
}