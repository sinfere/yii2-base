<?php
namespace dix\base\module\generator;


use Yii;
use yii\base\Object;

class CodeFile extends Object
{
    /**
     * The code file is new.
     */
    const OP_CREATE = 'create';
    /**
     * The code file already exists, and the new one may need to overwrite it.
     */
    const OP_OVERWRITE = 'overwrite';
    /**
     * The new code file and the existing one are identical.
     */
    const OP_SKIP = 'skip';

    const NEW_FILE_MODE = 0666;

    const NEW_DIR_MODE = 0777;


    /**
     * @var string an ID that uniquely identifies this code file.
     */
    public $id;
    /**
     * @var string the file path that the new code should be saved to.
     */
    public $path;
    /**
     * @var string the newly generated code content
     */
    public $content;
    /**
     * @var string the operation to be performed. This can be [[OP_CREATE]], [[OP_OVERWRITE]] or [[OP_SKIP]].
     */
    public $operation;

    /**
     * Constructor.
     * @param string $path the file path that the new code should be saved to.
     * @param string $content the newly generated code content.
     */
    public function __construct($path, $content)
    {
        $this->path = strtr($path, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
        $this->content = $content;
        $this->id = md5($this->path);
        if (is_file($path)) {
            $this->operation = file_get_contents($path) === $content ? self::OP_SKIP : self::OP_OVERWRITE;
        } else {
            $this->operation = self::OP_CREATE;
        }
    }

    /**
     * Saves the code into the file specified by [[path]].
     * @return string|boolean the error occurred while saving the code file, or true if no error.
     */
    public function save()
    {
        if ($this->operation === self::OP_CREATE) {
            $dir = dirname($this->path);
            if (!is_dir($dir)) {
                $mask = @umask(0);
                $result = @mkdir($dir, self::NEW_DIR_MODE, true);
                @umask($mask);
                if (!$result) {
                    return "Unable to create the directory '$dir'.";
                }
            }
        }
        if (@file_put_contents($this->path, $this->content) === false) {
            return "Unable to write the file '{$this->path}'.";
        } else {
            $mask = @umask(0);
            @chmod($this->path, self::NEW_FILE_MODE);
            @umask($mask);
        }

        return true;
    }

    /**
     * @return string the code file path relative to the application base path.
     */
    public function getRelativePath()
    {
        if (strpos($this->path, Yii::$app->basePath) === 0) {
            return substr($this->path, strlen(Yii::$app->basePath) + 1);
        } else {
            return $this->path;
        }
    }

}
