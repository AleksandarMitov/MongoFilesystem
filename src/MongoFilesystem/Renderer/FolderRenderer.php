<?php
/*
* Copyright (c) 2014 Alexander Mitov
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
* 
*  http://www.apache.org/licenses/LICENSE-2.0
* 
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/
namespace MongoFilesystem\Renderer;
use MongoFilesystem\MongoFolder;
use MongoFilesystem\MongoFile;
use \Twig_Loader_Filesystem;
use \Twig_Environment;
abstract class FolderRenderer
{
    /**
     * Holds the MongoFolder instance
     * @var MongoFolder
     */
    protected $folder;
    protected $twig;
    protected $twigLoader;
    /**
     * @var string The path to the views to be rendered
     */
    protected $pathToViews;
    /**
     * The file prefix of the temlplates to render
     * @var string
     */
    protected $typePrefix;
    /**
     * The costructor
     * @param MongoFolder $folder A MongoFolder intstance of the folder for rendering
     * @param string $pathToViews
     * @param string $typePrefix The file prefix of the temlplates to render
     */
    public function __construct(MongoFolder $folder, $pathToViews, $typePrefix) {
        $this->folder = $folder;
        $this->twigLoader = new Twig_Loader_Filesystem($pathToViews);
        $this->twig = new Twig_Environment($this->twigLoader);
        $this->pathToViews = $pathToViews;
        $this->typePrefix = $typePrefix;
    }
    public function render()
    {
        return $this->recursivelyRender($this->folder);
    }
    /**
     * Should return the rendered file
     * @param MongoFolder $folder
     */
    abstract protected function getFileRenderer(MongoFile $file);
    /**
     * Should return the rendered folder
     * @param MongoFolder $folder
     */
    abstract protected function getFolderRenderer(MongoFolder $folder);
    protected function recursivelyRender(MongoFolder $folder)
    {
        $context = array();
        $context["name"] = $folder->getFoldername();
        $context["size"] = $folder->getSize();
        $context["level"] = $folder->getLevel();
        $context["owner"] = $folder->getOwner();
        $context["permissions"] = $folder->getPermissions();
        $context["group"] = $folder->getGroup();
        $context["ID"] = (string) $folder->getID();
        $files = array();
        $subfolders = array();
        foreach($folder->getFiles() as $file)
        {
            $fileRenderer = $this->getFileRenderer($file, $this->pathToViews);
            $files[] = $fileRenderer->render();
        }
        foreach($folder->getSubfolders() as $subfolder)
        {
            $folderRenderer = $this->getFolderRenderer($subfolder, $this->pathToViews);
            $subfolders[] = $folderRenderer->render();
        }
        $context["files"] = implode(",", $files);
        $context["subfolders"] = implode(",", $subfolders);
        $result = $this->twig->render($this->typePrefix . "_folder.phtml", $context);
        return $result;
    }
}
