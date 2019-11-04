<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace models;

    /**
     * Cette classe gère les accès bdd pour les receivedes.
     */
    class Received extends \descartes\Model
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
            $receiveds = $this->_select('received', ['id' => $id]);

            return isset($receiveds[0]) ? $receiveds[0] : false;
        }

        /**
         * Retourne une liste de receivedes sous forme d'un tableau.
         *
         * @param int $limit  : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function list($limit, $offset)
        {
            return $this->_select('received', [], '', false, $limit, $offset);
        }

        /**
         * Cette fonction retourne les X dernières entrées triées par date.
         *
         * @param int $nb_entry : Nombre d'entrée à retourner
         *
         * @return array : Les dernières entrées
         */
        public function get_lasts_by_date($nb_entry)
        {
            return $this->_select('received', [], 'at', true, $nb_entry);
        }

        /**
         * Cette fonction retourne une liste des received sous forme d'un tableau.
         *
         * @param string $origin : Le numéro depuis lequel est envoyé le message
         *
         * @return array : La liste des received
         */
        public function get_by_origin($origin)
        {
            return $this->_select('received', ['origin' => $origin]);
        }

        /**
         * Retourne une liste de receivedes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets($ids)
        {
            $query = ' 
                SELECT * FROM received
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Retourne une liste de receivedes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM received
                WHERE id = :id';

            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une receivede.
         *
         * @param array $received : La receivede à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($received)
        {
            $result = $this->_insert('received', $received);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une receivede par son id.
         *
         * @param int   $id       : L'id de la received à modifier
         * @param array $received : Les données à mettre à jour pour la receivede
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $received)
        {
            return $this->_update('received', $received, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table.
         *
         * @return int : Le nombre d'entrées
         */
        public function count()
        {
            return $this->_count('received');
        }

        /**
         * Récupère le nombre de SMS envoyés pour chaque jour depuis une date.
         *
         * @param \DateTime $date : La date depuis laquelle on veux les SMS
         *
         * @return array : Tableau avec le nombre de SMS depuis la date
         */
        public function count_by_day_since($date)
        {
            $query = " 
                SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
                FROM received
                WHERE at > :date
                GROUP BY at_ymd
            ";

            $params = [
                'date' => $date,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Cette fonction retourne toutes les discussions, càd les numéros pour lesquels ont a a la fois un message et une réponse.
         */
        public function get_discussions()
        {
            $query = ' 
                    SELECT MAX(at) as at, number
                    FROM (SELECT at, destination as number FROM sendeds UNION (SELECT at, origin as number FROM received)) as discussions
                    GROUP BY origin
                    ORDER BY at DESC
            ';

            return $this->_run_query($query);
        }

        /**
         * Récupère les SMS reçus depuis une date.
         *
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_by_date($date)
        {
            $query = " 
                SELECT *
                FROM received
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                ORDER BY at ASC
            ";

            $params = [
                'date' => $date,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Récupère les SMS reçus depuis une date pour un numero.
         *
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @param $origin : Le numéro
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_for_origin_by_date($date, $origin)
        {
            $query = " 
                SELECT *
                FROM received
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                AND origin = :origin
                ORDER BY at ASC
            ";

            $params = [
                'date' => $date,
                'origin' => $origin,
            ];

            return $this->_run_query($query, $params);
        }
    }
