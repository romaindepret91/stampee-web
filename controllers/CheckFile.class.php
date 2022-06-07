<?php

/**
* Classe de vérification d'un fichier
*
*/
class CheckFile {
    
    public $error;

    public $filePath;
    
    /**
    * Sauvegarde les images téléversées par un membre dans un dossier associé après vérifications
    * @param array $file tableau des propriétés du fichier 
    *
    */
    public function checkFile($file, $userId) {
        if ($file['error'] > 0) {
            if($file['error'] === 2) $this->error = "Taille de fichier trop grande (doit être < 1Mo)";
            else {
                $this->error = "Erreur lors du transfert";
            }
        }
        if(!$this->error) {
            $maxsize = 1048576;
            if($file['size'] > $maxsize) $this->error = "Taille du fichier trop grande";
            $valid_ext = ['jpg' , 'jpeg' , 'gif' , 'png', 'webp'];
            $extension_upload = strtolower(substr(strrchr($file['name'],'.'),1));
            if (!in_array($extension_upload,$valid_ext)) $this->error .= " Extension du fichier non valide";
            if(!$this->error) {
                $folderPath = './assets/users/user' . $userId;
                if(file_exists($folderPath) === false) {
                    $createFolder = mkdir($folderPath, 0777, true);
                    if(!$createFolder) $this->error = "Échec de la création du dossier";
                    else {
                        $fileName = $userId . '-' . $file['name'];
                        $filePath = $folderPath . '/' . $fileName;
                        $result = move_uploaded_file($file['tmp_name'],$filePath);
                        if(!$result) $this->error = 'Échec lors du transfert de fichier';
                        else {
                            $this->filePath = $filePath;
                            return true;
                        }
                    }
                }
                else {
                    $fileName = $userId . '-' . $file['name'];
                    $filePath = $folderPath . '/' . $fileName;
                    $result = move_uploaded_file($file['tmp_name'],$filePath);
                    if(!$result) $this->error = 'Échec lors du transfert de fichier';
                    else {
                        $this->filePath = $filePath;
                        return true;
                    }
                }
            }
        }
        return $this->error;
    }
}