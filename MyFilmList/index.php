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
  <title>MyFilmList</title>
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

    .film-img {
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

  // Traitement de la suppression d'un film
  if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $requete = "DELETE FROM films WHERE id = :id";
    $stmt = $dbh->prepare($requete);
    $stmt->bindParam(':id', $_GET["id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
      $message = '
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        Le film a bien été supprimé
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

  // Traitement de l'ajout ou de la modification d'un film
  if (isset($_POST['submit_film'])) {
    // Récupération des données du formulaire
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $image_url = $_POST['image_url'];
    $titre = $_POST['titre'];
    $score = $_POST['score'] !== '' ? $_POST['score'] : null;
    $date_visionnage = $_POST['date_visionnage'] !== '' ? $_POST['date_visionnage'] : null;
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
        // Insertion d'un nouveau film
        $requete = "INSERT INTO films (image_url, titre, score, date_visionnage, commentaire, statut) 
                  VALUES (:image_url, :titre, :score, :date_visionnage, :commentaire, :statut)";
      } else {
        // Mise à jour d'un film existant
        $requete = "UPDATE films SET 
                  image_url = :image_url, 
                  titre = :titre, 
                  score = :score, 
                  date_visionnage = :date_visionnage, 
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
      $stmt->bindParam(':score', $score);
      $stmt->bindParam(':date_visionnage', $date_visionnage);
      $stmt->bindParam(':commentaire', $commentaire);
      $stmt->bindParam(':statut', $statut);

      if ($stmt->execute()) {
        $message = '
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          Le film a été ' . (empty($id) ? 'ajouté' : 'mis à jour') . ' avec succès
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

  // Récupération des films depuis la BDD
  $requete = "SELECT * FROM films $where_clause ORDER BY titre ASC";
  $stmt = $dbh->prepare($requete);
  $stmt->execute();
  $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-item" data-bs-toggle="modal" data-bs-target="#addFilmModal">
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
          <img src="../Capture d'écran 2024-01-10 201320.png" class="img-fluid w-100" alt="MyFilmList">
        </div>
      </div>
    </div>

    <div class="mt-4">
      <div class="row justify-content-center">
        <div class="col-md-11">
          <nav class="navbar navbar-expand-lg bg-primary">
            <div class="container-fluid">
              <a class="navbar-brand" href=".">MyFilmList</a>
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                  <li class="nav-item">
                    <a class="nav-link text-dark" href="../MyAnimeList/">MyAnimeList</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link text-dark" href="../MyGameList/">MyGameList</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link text-dark" href="../MySerieList/">MySerieList</a>
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
                  <a class="nav-link <?= $filter == 'all' ? 'active' : '' ?>" href="index.php?filter=all">All Films</a>
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
                <th>Nom Film</th>
                <th>Score</th>
                <th>Date de visionnage</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($films) > 0): ?>
                <?php foreach ($films as $index => $film): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                      <?php if (!empty($film['image_url'])): ?>
                        <img src="<?= htmlspecialchars($film['image_url']) ?>" alt="<?= htmlspecialchars($film['titre']) ?>"
                          class="film-img">
                      <?php else: ?>
                        <img src="placeholder.jpg" alt="No image" class="film-img">
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?= htmlspecialchars($film['titre']) ?></strong>
                      <br>
                      <small class="text-muted">Add notes</small>
                    </td>
                    <td class="text-center">
                      <span class="text-rating-<?= $film['score'] ? floor($film['score']) : '0' ?>"><?= $film['score'] ? $film['score'] : '-' ?></span>
                    </td>
                    <td><?= $film['date_visionnage'] ?? 'Non définie' ?></td>
                    <td><?= $film['statut'] ?></td>
                    <td>
                      <button class="btn btn-sm btn-warning edit-film" data-id="<?= $film['id'] ?>">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a href="index.php?action=delete&id=<?= $film['id'] ?>&filter=<?= $filter ?>"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Voulez-vous vraiment supprimer ce film?');">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center">Aucun film dans votre liste pour le moment.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal pour ajouter/modifier un film -->
  <div class="modal fade" id="filmModal" tabindex="-1" aria-labelledby="filmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filmModalLabel">Ajouter un film</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="filmForm" method="post" action="">
            <input type="hidden" name="id" id="film_id">

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
                <input type="number" class="form-control" id="score" name="score" min="1" max="10" step="0.1">
              </div>
            </div>

            <div class="mb-3 row">
              <label for="date_visionnage" class="col-sm-2 col-form-label">Date de visionnage</label>
              <div class="col-sm-10">
                <input type="date" class="form-control" id="date_visionnage" name="date_visionnage">
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
              <button type="submit" name="submit_film" class="btn btn-primary">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal pour Quick Add -->
  <div class="modal fade" id="addFilmModal" tabindex="-1" aria-labelledby="addFilmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addFilmModalLabel">Ajouter un nouveau film</h5>
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
            <!-- Champ Date de visionnage -->
            <div class="mb-3 row">
              <label for="quick_date_visionnage" class="col-sm-2 col-form-label">Date de visionnage</label>
              <div class="col-sm-10">
                <input type="date" class="form-control" id="quick_date_visionnage" name="date_visionnage" value="<?= date('Y-m-d') ?>">
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
                <input type="number" class="form-control" id="quick_score" name="score" min="1" max="10" step="0.1">
              </div>
            </div>

            <div class="text-end">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" name="submit_film" class="btn btn-primary">Ajouter</button>
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
    // Gestion de la modification d'un film
    document.addEventListener('DOMContentLoaded', function () {
      // Récupérer tous les boutons d'édition
      const editButtons = document.querySelectorAll('.edit-film');

      // Ajouter un écouteur d'événement à chaque bouton
      editButtons.forEach(button => {
        button.addEventListener('click', function () {
          const filmId = this.getAttribute('data-id');

          // Récupérer les données du film via AJAX
          fetch('get_film.php?id=' + filmId)
            .then(response => response.json())
            .then(data => {
              // Remplir le formulaire avec les données
              document.getElementById('film_id').value = data.id;
              document.getElementById('titre').value = data.titre;
              document.getElementById('image_url').value = data.image_url;
              document.getElementById('score').value = data.score;
              document.getElementById('date_visionnage').value = data.date_visionnage;
              document.getElementById('commentaire').value = data.commentaire;
              document.getElementById('statut').value = data.statut;

              // Mettre à jour le titre de la modal
              document.getElementById('filmModalLabel').textContent = 'Modifier le film';

              // Afficher la modal
              const filmModal = new bootstrap.Modal(document.getElementById('filmModal'));
              filmModal.show();
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