<?php

/**
 * Created by PhpStorm.
 * User: matpif
 * Date: 04/05/16
 * Time: 16:52
 */
class AdminController extends Controller
{

    const DEFAULT_PRIVILEGE = UtilisateurModel::PRIVILEGE_USER;

    /**
     * @var UtilisateurModel
     */
    private $_newUser;

    function __construct()
    {
        parent::__construct();
        $this->_url = '/Admin';
        $this->setTemplate('/admin/index.phtml');
        $this->_title = 'Administration';
        $this->_page = 'Admin';
        $this->setTemplateHeader('/admin/header.phtml');
    }

    public function newUserAction() {
        $get = Access::getRequest();
        if (isset($get['id'])) {
            $this->_newUser = (new UtilisateurCollection())->loadById($get['id']);
        }

        $this->setTemplate('/admin/addUser.phtml');
        $this->_title = 'Création de profil';
    }

    public function listUserAction() {
        $this->setTemplate('/admin/listUser.phtml');
        $this->_title = 'Liste des utilisateurs';
    }

    public function addUserAction(){
        $post = Access::getRequest();

        $utilisateur = new UtilisateurModel();

        if (isset($post['login'], $post['email'])) {

            if (isset($post['id'])) {
                $utilisateur->setAttribute('id', $post['id']);
            }
            
            $utilisateur->setAttribute('login', $post['login']);
            $utilisateur->setAttribute('email', $post['email']);
            $utilisateur->setAttribute('last_name', $post['lastName']);
            $utilisateur->setAttribute('first_name', $post['firstName']);
            $utilisateur->setAttribute('privilege', $post['privilege']);

            $newPassword = $utilisateur->newPassword();
            if (!isset($post['id'])) {
                $utilisateur->setPassword($newPassword);
            }
            
            if ($utilisateur->save()) {

                if (!isset($post['id'])) {
                    $sendMail = new SendMail();
                    $sendMail->setDestinataire($utilisateur->getAttribute('email'));
                    $sendMail->setTemplate('nouvelUtilisateur.phtml', ['mail' => $utilisateur->getAttribute('email'), 'password' => $newPassword]);
                    $sendMail->setObjet('Bienvenue sur Paris');

                    $sendMail->envoi();
                }
                
                $messages = new MessageManager();
                $messages->newMessage('L\'utilisateur a été sauvegardé correctement', Message::LEVEL_SUCCESS);
                $this->redirect($this->getUrlAction('listUser'));
            } else {
                $this->_newUser = $utilisateur;
                $messages = new MessageManager();
                $messages->newMessage('Un problème est survenue', Message::LEVEL_ERROR);
                $this->setTemplate('/admin/addUser.phtml');
            }
        } else {
            $this->_newUser = $utilisateur;
            $messages = new MessageManager();
            $messages->newMessage('Un des champs n\'est pas corect', Message::LEVEL_ERROR);
            $this->setTemplate('/admin/addUser.phtml');
        }
    }

    public function deleteUserAction() {
        $get = Access::getRequest();
        if (isset($get['id'])){
            /** @var UtilisateurModel $utilisateur */
            $utilisateur = (new UtilisateurCollection())->loadById($get['id']);
            $_utilisateur = Access::getInstance()->getCurrentUser();
            
            if ($utilisateur && $utilisateur->getAttribute('id') != $_utilisateur->getAttribute('id')) {
                if ($utilisateur->remove()) {
                    $messages = new MessageManager();
                    $messages->newMessage('L\'utilisateur a été correctement supprimé', Message::LEVEL_SUCCESS);
                } else {
                    $messages = new MessageManager();
                    $messages->newMessage('L\'utilisateur n\'a pas été correctement supprimé', Message::LEVEL_ERROR);
                }
            }
        }

        $this->redirect($this->getUrlAction('listUser'));
    }

    public function getCountModel($modelName) {
        $collection = Collection::getInstanceOf($modelName);
        return $collection->countElement();
    }

    /**
     * @return UtilisateurModel
     */
    public function getNewUser()
    {
        return ($this->_newUser)?$this->_newUser:new UtilisateurModel();
    }

    /**
     * @return Collection
     */
    public function getAllUser() {
        $_utilisateurCollection = new UtilisateurCollection();
        return $_utilisateurCollection->loadAll(["login" => Collection::SORT_ASC]);
    }
}