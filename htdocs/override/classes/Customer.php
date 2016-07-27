<?php

class Customer extends CustomerCore
{

    /**
     * @var Subscription
     */

    /**
     * @var Subscription
     */


    /**
     * Fonction très importante : Récupère tous les changements de status des commandes comportant un abonnement.
     * Tant qu'une commande a un statut actif ET qu'elle comporte un abonnement, ce dernier est actif à partir de la première date d'activation de la
     * commande (statut 2 -> Paiement accepté) Un ajout de statut est irréversible.
     */


    /**
     * Retourne les abonnements dont l'utilisateur courant bénéficie ou les siens
     * @param $type tierce pour avoir les abonnements qu'on lui offre ou 'himself' pour avoir les abonnements qu'il a lui même acheté.
     */



    /**
     * Retourne les abonnements achetés par l'utilisateur demandé
     * @param $user_id
     * @param l 'id de l'utilisateur qui a acheté les abonnements
     * @param bool $instituteAndArchiveOnly
     * @return array
     */

    /**
     * Récupère toutes les commandes possédant des abonnements , des produits papier ou des paiements avec BVR
     */

    /**
     * Ne garde que le premier état de l'historique de chaque commande (selon l'ordre défini dans la requette SQL)
     * @return Retourne une liste d'abonnements (objets Subscription)
     */

    /**
     * Récupère un seul et unique utilisateur ayant pu acheter un abonnement pour cette personne
     * S'il en existe plusieurs, le permier qui est trouvé sera utilisé. Dans la mesure où l'utilisateur bénéficiaire n'a pas de droits sur l'abonnement, il
     * n'importe pas de savoir de qui il le détient. Une mention est affichée dans la page abonnement si la personne bénéficie d'un abonnement tiers.
     * @return Retourne un tableau représentant la personne ayant acheté un abonnement pour lui
     */

    /**
     * Vérifie si le visiteur courant bénéficie d'avantages achetés par qqn d'autre
     * @param Une liste des personnes ayant acheté des abonnements Insituts
     * @return bool
     */

    /**
     * Vérifie les droits des abonnements "pro", qui ont droit à 3 comptes
     * Pour faire cette vérification on autorise les adresses e-mail ajoutées dans les champs personnalisés
     */

    /**
     * Vérifie que les ip qui sont insérées dans les notes du client dans le BO de prestashop sont strictement égales à l'ip du visiteur courant
     */




    /**
     * Abonne l'utilisateur au groupe Abonnés
     */

    /*
    * Retire l'utilisateur du groupe abonnés afin qu'il ai les droits habituels
    */




    /**
     * Récupère tous les bénéficiaires d'un abonnement (une commande comportant un abonnement doit être valide)
     */


    /**
     *    Retourne les adresses email des clients connectés aux newsletter via la création d'un compte ou via le module en page d'accueil
     */



    /**
     * @var Subscription
     */

    /**
     * @var Subscription
     */


    /**
     * Fonction très importante : Récupère tous les changements de status des commandes comportant un abonnement.
     * Tant qu'une commande a un statut actif ET qu'elle comporte un abonnement, ce dernier est actif à partir de la première date d'activation de la
     * commande (statut 2 -> Paiement accepté) Un ajout de statut est irréversible.
     */


    /**
     * Retourne les abonnements dont l'utilisateur courant bénéficie ou les siens
     * @param $type tierce pour avoir les abonnements qu'on lui offre ou 'himself' pour avoir les abonnements qu'il a lui même acheté.
     */



    /**
     * Retourne les abonnements achetés par l'utilisateur demandé
     * @param $user_id
     * @param l 'id de l'utilisateur qui a acheté les abonnements
     * @param bool $instituteAndArchiveOnly
     * @return array
     */

    /**
     * Récupère toutes les commandes possédant des abonnements , des produits papier ou des paiements avec BVR
     */

    /**
     * Ne garde que le premier état de l'historique de chaque commande (selon l'ordre défini dans la requette SQL)
     * @return Retourne une liste d'abonnements (objets Subscription)
     */

    /**
     * Récupère un seul et unique utilisateur ayant pu acheter un abonnement pour cette personne
     * S'il en existe plusieurs, le permier qui est trouvé sera utilisé. Dans la mesure où l'utilisateur bénéficiaire n'a pas de droits sur l'abonnement, il
     * n'importe pas de savoir de qui il le détient. Une mention est affichée dans la page abonnement si la personne bénéficie d'un abonnement tiers.
     * @return Retourne un tableau représentant la personne ayant acheté un abonnement pour lui
     */

    /**
     * Vérifie si le visiteur courant bénéficie d'avantages achetés par qqn d'autre
     * @param Une liste des personnes ayant acheté des abonnements Insituts
     * @return bool
     */

    /**
     * Vérifie les droits des abonnements "pro", qui ont droit à 3 comptes
     * Pour faire cette vérification on autorise les adresses e-mail ajoutées dans les champs personnalisés
     */

    /**
     * Vérifie que les ip qui sont insérées dans les notes du client dans le BO de prestashop sont strictement égales à l'ip du visiteur courant
     */




    /**
     * Abonne l'utilisateur au groupe Abonnés
     */

    /*
    * Retire l'utilisateur du groupe abonnés afin qu'il ai les droits habituels
    */




    /**
     * Récupère tous les bénéficiaires d'un abonnement (une commande comportant un abonnement doit être valide)
     */


    /**
     *    Retourne les adresses email des clients connectés aux newsletter via la création d'un compte ou via le module en page d'accueil
     */



    /**
     * @var Subscription
     */

    /**
     * @var Subscription
     */


    /**
     * Fonction très importante : Récupère tous les changements de status des commandes comportant un abonnement.
     * Tant qu'une commande a un statut actif ET qu'elle comporte un abonnement, ce dernier est actif à partir de la première date d'activation de la
     * commande (statut 2 -> Paiement accepté) Un ajout de statut est irréversible.
     */


    /**
     * Retourne les abonnements dont l'utilisateur courant bénéficie ou les siens
     * @param $type tierce pour avoir les abonnements qu'on lui offre ou 'himself' pour avoir les abonnements qu'il a lui même acheté.
     */



    /**
     * Retourne les abonnements achetés par l'utilisateur demandé
     * @param $user_id
     * @param l 'id de l'utilisateur qui a acheté les abonnements
     * @param bool $instituteAndArchiveOnly
     * @return array
     */

    /**
     * Récupère toutes les commandes possédant des abonnements , des produits papier ou des paiements avec BVR
     */

    /**
     * Ne garde que le premier état de l'historique de chaque commande (selon l'ordre défini dans la requette SQL)
     * @return Retourne une liste d'abonnements (objets Subscription)
     */

    /**
     * Récupère un seul et unique utilisateur ayant pu acheter un abonnement pour cette personne
     * S'il en existe plusieurs, le permier qui est trouvé sera utilisé. Dans la mesure où l'utilisateur bénéficiaire n'a pas de droits sur l'abonnement, il
     * n'importe pas de savoir de qui il le détient. Une mention est affichée dans la page abonnement si la personne bénéficie d'un abonnement tiers.
     * @return Retourne un tableau représentant la personne ayant acheté un abonnement pour lui
     */

    /**
     * Vérifie si le visiteur courant bénéficie d'avantages achetés par qqn d'autre
     * @param Une liste des personnes ayant acheté des abonnements Insituts
     * @return bool
     */

    /**
     * Vérifie les droits des abonnements "pro", qui ont droit à 3 comptes
     * Pour faire cette vérification on autorise les adresses e-mail ajoutées dans les champs personnalisés
     */

    /**
     * Vérifie que les ip qui sont insérées dans les notes du client dans le BO de prestashop sont strictement égales à l'ip du visiteur courant
     */




    /**
     * Abonne l'utilisateur au groupe Abonnés
     */

    /*
    * Retire l'utilisateur du groupe abonnés afin qu'il ai les droits habituels
    */




    /**
     * Récupère tous les bénéficiaires d'un abonnement (une commande comportant un abonnement doit être valide)
     */


    /**
     *    Retourne les adresses email des clients connectés aux newsletter via la création d'un compte ou via le module en page d'accueil
     */


}
