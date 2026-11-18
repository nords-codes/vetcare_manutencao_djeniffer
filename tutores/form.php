
<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

exigirLogin();

$id = (int) ($_GET['id'] ?? 0);

$r = [
    'nome' => '',
    'telefone' => '',
    'email' => '',
    'endereco' => '',
    'ativo' => 1
];

$erro = '';

if ($id > 0) {
    $s = $pdo->prepare("SELECT * FROM tutores WHERE id = ?");
    $s->execute([$id]);

    $tutor = $s->fetch(PDO::FETCH_ASSOC);

    if (!$tutor) {
        header('Location: index.php');
        exit;
    }

    $r = $tutor;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $ativo = (int) ($_POST['ativo'] ?? 1);

    if ($nome === '') {
        $erro = 'Informe o nome do tutor.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um endereço de e-mail válido.';
    } elseif (!in_array($ativo, [0, 1], true)) {
        $erro = 'Situação inválida.';
    } else {

        if ($id > 0) {

            $s = $pdo->prepare("
                UPDATE tutores
                SET nome = ?, telefone = ?, email = ?, endereco = ?, ativo = ?
                WHERE id = ?
            ");

            $s->execute([
                $nome,
                $telefone,
                $email,
                $endereco,
                $ativo,
                $id
            ]);

        } else {

            $s = $pdo->prepare("
                INSERT INTO tutores (nome, telefone, email, endereco)
                VALUES (?, ?, ?, ?)
            ");

            $s->execute([
                $nome,
                $telefone,
                $email,
                $endereco
            ]);

        }

        header('Location: index.php');
        exit;
    }

    $r = [
        'nome' => $nome,
        'telefone' => $telefone,
        'email' => $email,
        'endereco' => $endereco,
        'ativo' => $ativo
    ];
}

$titulo = $id > 0 ? 'Editar tutor' : 'Novo tutor';

include __DIR__ . '/../includes/header.php';

?>

<h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>

<section class="panel">

    <?php if ($erro): ?>
        <div class="alert">
            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="field">
            <label>Nome *</label>
            <input
                type="text"
                name="nome"
                value="<?= htmlspecialchars($r['nome'], ENT_QUOTES, 'UTF-8') ?>"
                required
            >
        </div>

        <div class="field">
            <label>Telefone</label>
            <input
                type="text"
                name="telefone"
                value="<?= htmlspecialchars($r['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <div class="field">
            <label>E-mail</label>
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($r['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <div class="field">
            <label>Endereço</label>
            <input
                type="text"
                name="endereco"
                value="<?= htmlspecialchars($r['endereco'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <?php if ($id > 0): ?>

            <div class="field">
                <label>Situação</label>

                <select name="ativo">

                    <option value="1" <?= $r['ativo'] ? 'selected' : '' ?>>
                        Ativo
                    </option>

                    <option value="0" <?= !$r['ativo'] ? 'selected' : '' ?>>
                        Inativo
                    </option>

                </select>
            </div>

        <?php endif; ?>

        <button type="submit">Salvar</button>

        <a class="btn secondary" href="index.php">Cancelar</a>

    </form>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
