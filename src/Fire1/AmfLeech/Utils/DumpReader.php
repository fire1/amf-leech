<?php
/*
* Copyright (C) 2015 Angel Zaprianov <me@fire1.eu>
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
* Project: AmfLeech
*
* Date: 3/18/2016
* Time: 11:57
*
* @author Angel Zaprianov <me@fire1.eu>
*/

namespace Fire1\AmfLeech\Utils;

use Symfony\Component\Finder\Finder;
use Fire1\AmfLeech\Utils\Exceptions\DumpReadException;
use Fire1\AmfLeech\Utils\Interfaces\DumpReadInterface;

/**
 * Class DumpReader
 * @package Fire1\AmfLeech\Utils
 */
class DumpReader implements DumpReadInterface
{
    /** Binary Stream container
     * @type string
     */
    protected $_container;
    /**
     * @type array
     */
    private $list = array();
    /**
     * @type Finder
     */
    protected $finder;

    /** Read AMF dumped files in folder
     * @param    string $dir_amf      Destination folder with files
     * @param string    $name_pattern Part of file name*
     */
    public function __construct($dir_amf, $name_pattern = "amf*")
    {
        $this->finder = (new Finder())->name($name_pattern)->in($dir_amf);
        $this->finder->sortByName();
    }

    /**
     * @return $this|Finder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /** Returns array file list
     * @return array
     */
    public function getList()
    {
        if (!empty($this->list) && is_array($this->list))
            return $this->list;

        $this->list = array();
        foreach ($this->getFinder() as $value):
            $this->list[] = $value;
        endforeach;
        natsort($this->list);

        return $this->list = array_values($this->list); // Returns array with re-ranged keys
    }

    /** Gets AMF dump content from given file index
     * @param int $index
     * @return string
     * @throws DumpReadException
     */
    public function readIndex($index = 0)
    {
        /* @var \SplFileInfo $splFile */
        $splFile = $this->getList()[ $index ];
        if (!is_a($splFile, 'SplFileInfo'))
            throw new DumpReadException("Cannot read index given DumpReader::readIndex({$index}) ");

        return $this->readDump($splFile);

    }

    /**
     * @param \SplFileInfo $fileInfo
     * @return string
     */
    protected function readDump(\SplFileInfo $fileInfo)
    {
        return $this->_container = file_get_contents($fileInfo->getRealPath());
    }

    /** Returns result last index read
     * @return string
     */
    public function getStream()
    {
        return $this->_container;
    }

    /** Returns result last index read
     * @return int
     */
    public function getLength()
    {
        return strlen($this->_container);
    }
}



