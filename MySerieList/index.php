<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="apple-touch-icon" sizes="57x57" href="../apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="../apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="../apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="../apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="../apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="../apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="../apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="../apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="../favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
  <link rel="manifest" href="../manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="../ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  <title>MySerieList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    .sidebar {
      background-color: #f8f9fa;
      border-right: 1px solid #dee2e6;
      height: auto;
      position: fixed;
      top: 50%;
      transform: translateY(-50%);
      width: auto;
      z-index: 100;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 10px 0;
    }

    .sidebar-item {
      margin-bottom: 10px 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: #6c757d;
      transition: all 0.3s;
    }

    .sidebar-item:hover {
      color: #0d6efd;
    }

    .sidebar-item i {
      font-size: 1.5rem;
      margin-bottom: 5px;
    }

    .sidebar-item span {
      font-size: 0.7rem;
      text-align: center;
    }

    .main-content {
      margin-left: 60px;
    }

    .filter-tabs {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      padding: 10px 0;
    }

    .filter-tabs .nav-link {
      color: #6c757d;
      border: none;
      padding: 8px 16px;
    }

    .filter-tabs .nav-link.active {
      color: #0d6efd;
      background-color: transparent;
      border-bottom: 2px solid #0d6efd;
    }

    .serie-img {
      width: 60px;
      height: 85px;
      object-fit: cover;
    }

    .text-rating-9,
    .text-rating-10 {
      color: #28a745;
    }

    .text-rating-7,
    .text-rating-8 {
      color: #17a2b8;
    }

    .text-rating-5,
    .text-rating-6 {
      color: #ffc107;
    }

    .text-rating-1,
    .text-rating-2,
    .text-rating-3,
    .text-rating-4 {
      color: #dc3545;
    }
  </style>
</head>

<body>
  <?php
  // Inclusion du fichier de connexion à la BDD
  require_once '../config/connection.php';

  // Gestion des messages d'alerte
  $message = '';

  // Traitement de la suppression d'une serie
  if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $requete = "DELETE FROM series WHERE id = :id";
    $stmt = $dbh->prepare($requete);
    $stmt->bindParam(':id', $_GET["id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
      $message = '
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        La serie a bien été supprimé
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>';
    } else {
      $message = '
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Une erreur est survenue lors de la suppression
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>';
    }
  }

  // Traitement de l'ajout ou de la modification d'une serie
  if (isset($_POST['submit_serie'])) {
    // Récupération des données du formulaire
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $image_url = $_POST['image_url'];
    $titre = $_POST['titre'];
    $score = $_POST['score'] !== '' ? $_POST['score'] : null;
    $avancement = $_POST['avancement'];
    $date_debut = $_POST['date_debut'] !== '' ? $_POST['date_debut'] : null;
    $date_fin = $_POST['date_fin'] !== '' ? $_POST['date_fin'] : null;
    $commentaire = $_POST['commentaire'];
    $statut = $_POST['statut'];

    // Validation des données
    $errors = [];
    if (empty($titre)) {
      $errors[] = "Le titre est obligatoire";
    }

    // Si pas d'erreurs, enregistrement dans la BDD
    if (empty($errors)) {
      if (empty($id)) {
        // Insertion d'une nouvelle serie
        $requete = "INSERT INTO series (image_url, titre, score, avancement, date_debut, date_fin, commentaire, statut) 
                  VALUES (:image_url, :titre, :score, :avancement, :date_debut, :date_fin, :commentaire, :statut)";
      } else {
        // Mise à jour d'une serie existante
        $requete = "UPDATE series SET 
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

      if (!empty($id)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      }

      $stmt->bindParam(':image_url', $image_url);
      $stmt->bindParam(':titre', $titre);
      $stmt->bindParam(':score', $score, PDO::PARAM_INT);
      $stmt->bindParam(':avancement', $avancement, PDO::PARAM_INT);
      $stmt->bindParam(':date_debut', $date_debut);
      $stmt->bindParam(':date_fin', $date_fin);
      $stmt->bindParam(':commentaire', $commentaire);
      $stmt->bindParam(':statut', $statut);

      if ($stmt->execute()) {
        $message = '
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          La serie a été ' . (empty($id) ? 'ajouté' : 'mis à jour') . ' avec succès
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
      } else {
        $message = '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          Une erreur est survenue lors de l\'enregistrement
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
      }
    } else {
      // Affichage des erreurs
      $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul>';
      foreach ($errors as $error) {
        $message .= '<li>' . $error . '</li>';
      }
      $message .= '</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
  }

  // Récupération des filtres
  $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

  // Construction de la requête SQL en fonction du filtre
  $where_clause = '';
  switch ($filter) {
    case 'watching':
      $where_clause = "WHERE statut = 'En cours'";
      break;
    case 'completed':
      $where_clause = "WHERE statut = 'Terminé'";
      break;
    case 'onhold':
      $where_clause = "WHERE statut = 'Prévu'";
      break;
    case 'dropped':
      $where_clause = "WHERE statut = 'Abandonné'";
      break;
    case 'plan':
      $where_clause = "WHERE statut = 'Pas commencé'";
      break;
    default:
      $where_clause = '';
  }

  // Récupération des series depuis la BDD
  $requete = "SELECT * FROM series $where_clause ORDER BY titre ASC";
  $stmt = $dbh->prepare($requete);
  $stmt->execute();
  $series = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-item" data-bs-toggle="modal" data-bs-target="#addserieModal">
      <i class="fas fa-plus"></i>
      <span>Quick Add</span>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <?php if (!empty($message))
      echo $message; ?>

    <div class="mt-4">
      <div class="justify-content-center row">
        <div class="col-md-11">
          <img src="../Capture d'écran 2024-01-10 201320.png" class="img-fluid w-100" alt="MySerieList">
        </div>
      </div>
    </div>

    <div class="mt-4">
      <div class="row justify-content-center">
        <div class="col-md-11">
          <nav class="navbar navbar-expand-lg bg-warning">
            <div class="container-fluid">
              <a class="navbar-brand" href=".">MySerieList</a>
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                  <li class="nav-item">
                    <a class="nav-link text-dark" href="../MyAnimeList/">MyAnimeList</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link text-dark" href="../MyFilmList/">MyFilmList</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link text-dark" href="../MyGameList/">MyGameList</a>
                  </li>
                </ul>
              </div>
            </div>
          </nav>
        </div>
      </div>
    </div>

      <div class="row justify-content-center">
        <div class="col-md-11">
          <div class="filter-tabs">
            <div class="container">
              <ul class="nav nav-pills">
                <li class="nav-item">
                  <a class="nav-link <?= $filter == 'all' ? 'active' : '' ?>" href="index.php?filter=all">All serie</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $filter == 'watching' ? 'active' : '' ?>"
                    href="index.php?filter=watching">Currently Watching</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $filter == 'completed' ? 'active' : '' ?>"
                    href="index.php?filter=completed">Completed</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $filter == 'onhold' ? 'active' : '' ?>" href="index.php?filter=onhold">On
                    Hold</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $filter == 'dropped' ? 'active' : '' ?>"
                    href="index.php?filter=dropped">Dropped</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $filter == 'plan' ? 'active' : '' ?>" href="index.php?filter=plan">Plan to
                    Watch</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    <div class="mt-4">
      <div class="row justify-content-center">
        <div class="col-md-11">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Image</th>
                <th>Nom serie</th>
                <th>Score</th>
                <th>Type</th>
                <th>Progression</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($series) > 0): ?>
                <?php foreach ($series as $index => $serie): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                      <?php if (!empty($serie['image_url'])): ?>
                        <img src="<?= htmlspecialchars($serie['image_url']) ?>" alt="<?= htmlspecialchars($serie['titre']) ?>"
                          class="serie-img">
                      <?php else: ?>
                        <img src="placeholder.jpg" alt="No image" class="serie-img">
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?= htmlspecialchars($serie['titre']) ?></strong>
                      <br>
                      <small class="text-muted">Add notes</small>
                    </td>
                    <td class="text-center">
                      <span
                        class="text-rating-<?= $serie['score'] ? $serie['score'] : '0' ?>"><?= $serie['score'] ? $serie['score'] : '-' ?></span>
                    </td>
                    <td>TV</td>
                    <td><?= $serie['avancement'] ?>     <?= $serie['avancement'] == 1 ? 'épisode' : 'épisodes' ?></td>
                    <td>
                      <button class="btn btn-sm btn-warning edit-serie" data-id="<?= $serie['id'] ?>">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a href="index.php?action=delete&id=<?= $serie['id'] ?>&filter=<?= $filter ?>"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Voulez-vous vraiment supprimer cet serie?');">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center">Aucune serie dans votre liste pour le moment.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal pour ajouter/modifier une serie -->
  <div class="modal fade" id="serieModal" tabindex="-1" aria-labelledby="serieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="serieModalLabel">Ajouter une serie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="serieForm" method="post" action="">
            <input type="hidden" name="id" id="serie_id">

            <div class="mb-3 row">
              <label for="titre" class="col-sm-2 col-form-label">Titre*</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="titre" name="titre" required>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="image_url" class="col-sm-2 col-form-label">URL de l'image</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="image_url" name="image_url">
              </div>
            </div>

            <div class="mb-3 row">
              <label for="score" class="col-sm-2 col-form-label">Score (1-10)</label>
              <div class="col-sm-10">
                <input type="number" class="form-control" id="score" name="score" min="1" max="10">
              </div>
            </div>

            <div class="mb-3 row">
              <label for="avancement" class="col-sm-2 col-form-label">Épisodes vus</label>
              <div class="col-sm-10">
                <input type="number" class="form-control" id="avancement" name="avancement" min="0" value="0">
              </div>
            </div>

            <div class="mb-3 row">
              <label for="date_debut" class="col-sm-2 col-form-label">Date de début</label>
              <div class="col-sm-10">
                <input type="date" class="form-control" id="date_debut" name="date_debut">
              </div>
            </div>

            <div class="mb-3 row">
              <label for="date_fin" class="col-sm-2 col-form-label">Date de fin</label>
              <div class="col-sm-10">
                <input type="date" class="form-control" id="date_fin" name="date_fin">
              </div>
            </div>

            <div class="mb-3 row">
              <label for="statut" class="col-sm-2 col-form-label">Statut</label>
              <div class="col-sm-10">
                <select class="form-select" id="statut" name="statut">
                  <option value="Pas commencé">Pas commencé</option>
                  <option value="En cours">En cours</option>
                  <option value="Abandonné">Abandonné</option>
                  <option value="Prévu">Prévu</option>
                  <option value="Terminé">Terminé</option>
                </select>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="commentaire" class="col-sm-2 col-form-label">Commentaires</label>
              <div class="col-sm-10">
                <textarea class="form-control" id="commentaire" name="commentaire" rows="3"></textarea>
              </div>
            </div>

            <div class="text-end">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" name="submit_serie" class="btn btn-primary">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal pour Quick Add -->
  <div class="modal fade" id="addserieModal" tabindex="-1" aria-labelledby="addserieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addserieModalLabel">Ajouter une nouvelle serie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="quickAddForm" method="post" action="">
            <input type="hidden" name="id" value="">
            <!-- Champ Titre -->
            <div class="mb-3 row">
              <label for="quick_titre" class="col-sm-2 col-form-label">Titre*</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="quick_titre" name="titre" required>
              </div>
            </div>
            <!-- Champ URL de l'image -->
            <div class="mb-3 row">
              <label for="quick_image_url" class="col-sm-2 col-form-label">URL de l'image</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="quick_image_url" name="image_url">
              </div>
            </div>
            <!-- Champ Commentaire -->
          <div class="mb-3 row">
            <label for="quick_commentaire" class="col-sm-2 col-form-label">Commentaire</label>
            <div class="col-sm-10">
              <textarea class="form-control" id="quick_commentaire" name="commentaire" rows="3"></textarea>
            </div>
          </div>
                    <!-- Champ Date de Début -->
                    <div class="mb-3 row">
            <label for="quick_date_debut" class="col-sm-2 col-form-label">Date de début</label>
            <div class="col-sm-10">
              <input type="date" class="form-control" id="quick_date_debut" name="date_debut" value="<?= date('Y-m-d') ?>">
            </div>
          </div>

          <!-- Champ Date de Fin -->
          <div class="mb-3 row">
            <label for="quick_date_fin" class="col-sm-2 col-form-label">Date de fin</label>
            <div class="col-sm-10">
              <input type="date" class="form-control" id="quick_date_fin" name="date_fin">
            </div>
          </div>
          <!-- Champ Statut -->
            <div class="mb-3 row">
              <label for="quick_statut" class="col-sm-2 col-form-label">Statut</label>
              <div class="col-sm-10">
                <select class="form-select" id="quick_statut" name="statut">
                  <option value="Pas commencé">Pas commencé</option>
                  <option value="En cours">En cours</option>
                  <option value="Abandonné">Abandonné</option>
                  <option value="Prévu">Prévu</option>
                  <option value="Terminé">Terminé</option>
                </select>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="quick_score" class="col-sm-2 col-form-label">Score</label>
              <div class="col-sm-10">
                <input type="number" class="form-control" id="quick_score" name="score" min="1" max="10">
              </div>
            </div>

            <div class="mb-3 row">
              <label for="quick_avancement" class="col-sm-2 col-form-label">Épisodes vus</label>
              <div class="col-sm-10">
                <input type="number" class="form-control" id="quick_avancement" name="avancement" min="0" value="0">
              </div>
            </div>

            <div class="text-end">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" name="submit_serie" class="btn btn-primary">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <script>
    // Gestion de la modification d'une serie
    document.addEventListener('DOMContentLoaded', function () {
      // Récupérer tous les boutons d'édition
      const editButtons = document.querySelectorAll('.edit-serie');

      // Ajouter un écouteur d'événement à chaque bouton
      editButtons.forEach(button => {
        button.addEventListener('click', function () {
          const serieId = this.getAttribute('data-id');

          // Récupérer les données de la serie via AJAX
          fetch('get_serie.php?id=' + serieId)
            .then(response => response.json())
            .then(data => {
              // Remplir le formulaire avec les données
              document.getElementById('serie_id').value = data.id;
              document.getElementById('titre').value = data.titre;
              document.getElementById('image_url').value = data.image_url;
              document.getElementById('score').value = data.score;
              document.getElementById('avancement').value = data.avancement;
              document.getElementById('date_debut').value = data.date_debut;
              document.getElementById('date_fin').value = data.date_fin;
              document.getElementById('commentaire').value = data.commentaire;
              document.getElementById('statut').value = data.statut;

              // Mettre à jour le titre de la modal
              document.getElementById('serieModalLabel').textContent = 'Modifier la serie';

              // Afficher la modal
              const serieModal = new bootstrap.Modal(document.getElementById('serieModal'));
              serieModal.show();
            })
            .catch(error => {
              console.error('Erreur:', error);
              alert('Une erreur est survenue lors de la récupération des données');
            });
        });
      });
    });
  </script>
</body>

</html>