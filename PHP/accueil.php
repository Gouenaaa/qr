<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/accueil.css">
    <title>QR</title>
    <?php
        session_start();
        include "./cnx.php";
    ?>
</head>
<body>
    <div id="head">
        <div id="divLogo">
            <a href="./accueil.php"><img id="imgLogo" src="../MEDIAS/logoContour.png"></a>
        </div>
        <div id="divProfil">
            <a href="./profil.php?idU=".<?php $_SESSION["idU"]?>><?php echo $_SESSION["loginU"]; ?></a>
            
        </div>
    </div>
        <?php
            //Requete pour obtenir tous les questionnaires
            $lesQuesionnaires = $cnx->prepare("SELECT idQuestionnaire, libelleQuestionnaire, cheminImageQuestionnaire
            FROM questionnaire");
            $lesQuesionnaires->execute();

            //Requete pour savoir les questionnaires déjà terminés
            $qcmFini = $cnx->prepare("SELECT idQuestionnaire
            FROM qcmfait
            WHERE idEtudiant = :idU
            GROUP BY idQuestionnaire");
            $qcmFini->bindValue(":idU", $_SESSION["idU"], PDO::PARAM_INT);
            $qcmFini->execute();
            $qcmFini = $qcmFini->fetchAll(PDO::FETCH_ASSOC);
            

            //On affiche les questionnaires un par un
            $nbQ = 1;
            foreach($lesQuesionnaires->fetchAll(PDO::FETCH_ASSOC) as $ligne){
                if($nbQ == 1){
                    echo "<div class='divQuestionnaires'>";
                }
                echo "<div class='divQuestionnaire'>";
                echo "<div class='divImgQuestionnaire'>";
                echo "<a href='./questionnaire.php?idQuestionnaire=".$ligne["idQuestionnaire"]."'>";
                echo "<img class='imgQuestionnaire' src='".$ligne["cheminImageQuestionnaire"]."'>";
                echo "</a>";
                echo "</div>";
                echo $ligne["libelleQuestionnaire"];
                //Si le questionnaire a déjà été terminé
                foreach($qcmFini as $qcm){
                    if($ligne["idQuestionnaire"] == $qcm["idQuestionnaire"]){
                        //On récupère le dernier passage
                        $qcmFait = $cnx->prepare("SELECT dateFait, points
                        FROM qcmfait
                        WHERE dateFait = (SELECT MAX(dateFait) 
                            FROM qcmfait 
                            WHERE idEtudiant = :idU 
                            AND idQuestionnaire = :idQ)
                        AND idFait = (SELECT MAX(idFait)
                            FROM qcmfait
                            WHERE idEtudiant = :idU 
                            AND idQuestionnaire = :idQ)");
                        $qcmFait->bindValue(":idU", $_SESSION["idU"], PDO::PARAM_INT);
                        $qcmFait->bindValue(":idQ", $ligne["idQuestionnaire"], PDO::PARAM_INT);
                        $qcmFait->execute();
                        $qcmFait = $qcmFait->fetch(PDO::FETCH_ASSOC);

                        //On compte le nombre de question du questionnaire
                        $nbQuestions = $cnx->prepare("SELECT COUNT(idQuestion)
                        FROM questionquestionnaire
                        WHERE idQuestionnaire = :idQ");
                        $nbQuestions->bindValue(":idQ", $ligne["idQuestionnaire"], PDO::PARAM_INT);
                        $nbQuestions->execute();
                        $nbQuestions = $nbQuestions->fetch(PDO::FETCH_NUM);
                        //On affiche les infos
                        echo "<span>Dernier passage: ".$qcmFait["dateFait"]."</span>";
                        echo "<span>Note: ".$qcmFait["points"]."/".$nbQuestions[0]."</span>";
                        echo "<a href='./historique.php?idQ=".$ligne["idQuestionnaire"]."'>Historique</a>";
                    }
                }
                echo "</div>";
                if($nbQ == 4){
                    echo "</div>";
                    $nbQ = 1;
                }
                else{
                    $nbQ = $nbQ + 1;
                }
            }
            if($nbQ != 4){
                echo "</div>";
            }

        ?>
</body>
</html>