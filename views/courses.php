<?php
$messages = [
    'created' => 'Course added successfully.',
    'updated' => 'Course updated successfully.',
    'deleted' => 'Course deleted successfully.',
];
$saved = $_GET['saved'] ?? '';
?>

<h1>Course Management</h1>

<?php if (isset($messages[$saved])): ?>
    <p style="color:green;"><?= $messages[$saved] ?></p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?page=courses">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="course_id" value="<?= htmlspecialchars($editCourse['course_id'] ?? '') ?>">

    <label>Course Code<br>
        <input type="text" name="course_code" value="<?= htmlspecialchars($editCourse['course_code'] ?? '') ?>" placeholder="e.g. BSIT" required>
    </label><br>
    <label>Course Name<br>
        <input type="text" name="course_name" size="50" value="<?= htmlspecialchars($editCourse['course_name'] ?? '') ?>" placeholder="e.g. Bachelor of Science in Information Technology" required>
    </label><br><br>

    <button type="submit"><?= !empty($editCourse['course_id']) ? 'UPDATE' : 'SAVE' ?></button>
    <a href="index.php?page=courses"><button type="button">CLEAR</button></a>
</form>

<hr>

<form method="get" action="index.php">
    <input type="hidden" name="page" value="courses">
    <input type="text" name="search" placeholder="Search by code or name..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px; width:100%;">
    <thead>
        <tr><th>Code</th><th>Course Name</th><th>Patients Using It</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (empty($courses)): ?>
            <tr><td colspan="4">No courses found.</td></tr>
        <?php else: foreach ($courses as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['course_code']) ?></td>
                <td><?= htmlspecialchars($c['course_name']) ?></td>
                <td><?= (int)$c['patient_count'] ?></td>
                <td>
                    <a href="index.php?page=courses&action=edit&id=<?= $c['course_id'] ?>">Edit</a> |
                    <form method="post" action="index.php?page=courses" style="display:inline;" onsubmit="return confirm('Delete this course?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>