<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace models;

    /**
     * Cette classe gère les accès bdd pour les contactes.
     */
    class Contact extends \descartes\Model
    {
        /**
         * Retourne une entrée par son id.
         *
         * @param int $id : L'id de l'entrée
         *
         * @return array : L'entrée
         */
        public function get($id)
        {
            $contacts = $this->_select('contact', ['id' => $id]);

            return isset($contacts[0]) ? $contacts[0] : false;
        }

        /**
         * Retourne une entrée par son numéro de tel.
         *
         * @param string $number : Le numéro de tél
         *
         * @return array : L'entrée
         */
        public function get_by_number($number)
        {
            $contacts = $this->_select('contact', ['number' => $number]);

            return isset($contacts[0]) ? $contacts[0] : false;
        }
        
        /**
         * Return a contact for a user by a number
         * @param int $id_user : user id
         * @param string $number : Contact number
         * @return array
         */
        public function get_by_number_and_user($number, $id_user)
        {
            return $this->_select_one('contact', ['number' => $number, 'id_user' => $id_user]);
        }

        /**
         * Retourne une entrée par son numéro de tel.
         *
         * @param string $name : Le numéro de tél
         *
         * @return array : L'entrée
         */
        public function get_by_name($name)
        {
            $contacts = $this->_select('contact', ['name' => $name]);

            return isset($contacts[0]) ? $contacts[0] : false;
        }

        /**
         * Get contacts of a particular group.
         *
         * @param int $id_group : Id of the group we want contacts for
         *
         * @return array
         */
        public function get_by_group($id_group)
        {
            return $this->_select('contact', ['id_group' => $id_group]);
        }

        /**
         * List contacts for a user
         * @param int $id_user : user id
         * @param mixed(int|bool) $nb_entry : Number of entry to return
         * @param mixed(int|bool) $page     : Pagination, will offset $nb_entry * $page results
         * @return array
         */
        public function list_for_user($id_user, $limit, $offset)
        {
            return $this->_select('contact', ['id_user' => $id_user], null, false, $limit, $offset);
        }

        /**
         * Retourne une liste de contactes sous forme d'un tableau.
         * @param int $id_user : user id
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         * @return array : La liste des entrées
         */
        public function gets_for_user($id_user, $ids)
        {
            $query = ' 
                SELECT * FROM contact
                WHERE id_user = :id_user
                AND ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];
            $params['id_user'] = $id_user; 

            return $this->_run_query($query, $params);
        }

        /**
         * Supprimer un contact par son id.
         *
         * @param array $id : un ou plusieurs id d'entrées à supprimer
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM contact
                WHERE id = :id';

            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une contacte.
         *
         * @param array $contact : La contacte à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($contact)
        {
            $result = $this->_insert('contact', $contact);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une contacte par son id.
         *
         * @param int   $id      : L'id de la contact à modifier
         * @param array $contact : Les données à mettre à jour pour la contacte
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $contact)
        {
            return $this->_update('contact', $contact, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table contact.
         *
         * @return int : Le nombre de contact
         */
        public function count()
        {
            return $this->_count('contact');
        }
    }
