<?php
// Initialisation des variables
$anime = [
    'id' => '',
    'image_url' => '',
    'titre' => '',
    'score' => '',
    'avancement' => 0,
    'date_debut' => '',
    'date_fin' => '',
    'commentaire' => '',
    'statut' => 'Pas commencé'
];

// Si on est en mode édition, récupérer les données de l'anime
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $requete = "SELECT * FROM animes WHERE id = :id";
    $stmt = $dbh->prepare($requete);
    $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $anime = $result;
    }
}

// Traitement du formulaire lors de la soumission
if (isset($_POST['submit'])) {
    // Récupération des données du formulaire
    $anime = [
        'id' => isset($_POST['id']) ? $_POST['id'] : '',
        'image_url' => $_POST['image_url'],
        'titre' => $_POST['titre'],
        'score' => $_POST['score'] !== '' ? $_POST['score'] : null,
        'avancement' => $_POST['avancement'],
        'date_debut' => $_POST['date_debut'] !== '' ? $_POST['date_debut'] : null,
        'date_fin' => $_POST['date_fin'] !== '' ? $_POST['date_fin'] : null,
        'commentaire' => $_POST['commentaire'],
        'statut' => $_POST['statut']
    ];
    
    // Validation des données
    $errors = [];
    if (empty($anime['titre'])) {
        $errors[] = "Le titre est obligatoire";
    }
    
    // Si pas d'erreurs, enregistrement dans la BDD
    if (empty($errors)) {
        if (empty($anime['id'])) {
            // Insertion d'un nouvel anime
            $requete = "INSERT INTO animes (image_url, titre, score, avancement, date_debut, date_fin, commentaire, statut) 
                      VALUES (:image_url, :titre, :score, :avancement, :date_debut, :date_fin, :commentaire, :statut)";
        } else {
            // Mise à jour d'un anime existant
            $requete = "UPDATE animes SET 
                      image_url = :image_url, 
                      titre = :titre, 
                      score = :score, 
                      avancement = :avancement, 
                      date_debut = :date_debut, 
                      date_fin = :date_fin, 
                      commentaire = :commentaire, 
                      statut = :statut 
                      WHERE id = :id";
        }
        
        $stmt = $dbh->prepare($requete);
        
        if (!empty($anime['id'])) {
            $stmt->bindParam(':id', $anime['id'], PDO::PARAM_INT);
        }
        
        $stmt->bindParam(':image_url', $anime['image_url']);
        $stmt->bindParam(':titre', $anime['titre']);
        $stmt->bindParam(':score', $anime['score'], PDO::PARAM_INT);
        $stmt->bindParam(':avancement', $anime['avancement'], PDO::PARAM_INT);
        $stmt->bindParam(':date_debut', $anime['date_debut']);
        $stmt->bindParam(':date_fin', $anime['date_fin']);
        $stmt->bindParam(':commentaire', $anime['commentaire']);
        $stmt->bindParam(':statut', $anime['statut']);
        
        if ($stmt->execute()) {
            $message = '
            <div class="alert alert-success" role="alert">
              L\'anime a été ' . (empty($anime['id']) ? 'ajouté' : 'mis à jour') . ' avec succès
            </div>';
            
            // Redirection vers la page principale après un court délai
            header("Refresh: 2; url=index.php");
            echo $message;
            exit;
        } else {
            $message = '
            <div class="alert alert-danger" role="alert">
              Une erreur est survenue lors de l\'enregistrement
            </div>';
        }
    } else {
        // Affichage des erreurs
        $message = '<div class="alert alert-danger" role="alert"><ul>';
        foreach ($errors as $error) {
            $message .= '<li>' . $error . '</li>';
        }
        $message .= '</ul></div>';
    }
}
?>

<div class="card">
  <div class="card-header bg-primary text-white">
    <?= empty($anime['id']) ? 'Ajouter un nouvel anime' : 'Modifier l\'anime' ?>
  </div>
  <div class="card-body">
    <?php if (!empty($message)) echo $message; ?>
    
    <form method="post" action="">
      <input type="hidden" name="id" value="<?= htmlspecialchars($anime['id']) ?>">
      
      <div class="mb-3 row">
        <label for="titre" class="col-sm-2 col-form-label">Titre*</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="titre" name="titre" required value="<?= htmlspecialchars($anime['titre']) ?>">
        </div>
      </div>
      
      <div class="mb-3 row">
        <label for="image_url" class="col-sm-2 col-form-label">URL de l'image</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($anime['image_url']) ?>">
        </div>
      </div>
      
      <div class="mb-3 row">
        <label for="score" class="col-sm-2 col-form-label">Score (1-10)</label>
        <div class="col-sm-10">
          <input type="number" class="form-control" id="score" name="score" min="1" max="10" value="<?= htmlspecialchars($anime['score']) ?>">
        </div>
      </div>
      
      <div class="mb-3 row">
        <label for="avancement" class="col-sm-2 col-form-label">Épisodes vus</label>
        <div class="col-sm-10">
          <input type="number" class="form-control" id="avancement" name="avancement" min="0" value="<?= htmlspecialchars($anime['avancement']) ?>">
        </div>
      </div>
      
      <div class="mb-3 row">
        <label for="date_debut" class="col-sm-2 col-form-label">Date de début</label>
        <div class="col-sm-10">
          <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?= htmlspecialchars($anime['date_debut']) ?>">
        </div>
      </div>
      
      <div class="mb-3 row">
        <label for="date_fin" class="col-sm-2 col-form-label">Date de fin</label>
        <div class="col-sm-10">
          <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?= htmlspecialchars($anime['date_fin']) ?>">
        </div>
      </div>
      
      <div class="mb-3 row">
        <label for="statut" class="col-sm-2 col-form-label">Statut</label>
        <div class="col-sm-10">
          <select class="form-select" id="statut" name="statut">
            <option value="Pas commencé" <?= $anime['statut'] == 'Pas commencé' ? 'selected' : '' ?>>Pas commencé</option>
            <option value="En cours" <?= $anime['statut'] == 'En cours' ? 'selected' : '' ?>>En cours</option>
            <option value="Abandonné" <?= $anime['statut'] == 'Abandonné' ? 'selected' : '' ?>>Abandonné</option>
            <option value="Prévu" <?= $anime['statut'] == 'Prévu' ? 'selected' : '' ?>>Prévu</option>
            <option value="Terminé" <?= $anime['statut'] == 'Terminé' ? 'selected' : '' ?>>Terminé</option>
          </select>
        </div>
      </div>
      
      <div class="mb-3 row">
        <label for="commentaire" class="col-sm-2 col-form-label">Commentaires</label>
        <div class="col-sm-10">
          <textarea class="form-control" id="commentaire" name="commentaire" rows="3"><?= htmlspecialchars($anime['commentaire']) ?></textarea>
        </div>
      </div>
      
      <div class="mb-3 row">
        <div class="col-sm-10 offset-sm-2">
          <button type="submit" name="submit" class="btn btn-primary">Enregistrer</button>
          <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
      </div>
    </form>
  </div>
</div>