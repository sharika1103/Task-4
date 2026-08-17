<?php
require_once "auth.php";
$sql = "SELECT id, name, email, last_login, status, created_at
        FROM users
        ORDER BY last_login DESC, created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>User Management</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                User Management
            </h2>
            <p class="text-muted mb-0">
                Welcome,
                <?php echo htmlspecialchars($currentUser["name"]); ?>
            </p>
        </div>
        <a
            href="logout.php"
            class="btn btn-outline-secondary"
            title="Logout"
        >
            Logout
        </a>
    </div>
    <?php if (isset($_GET["success"])): ?>
        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            <?php echo htmlspecialchars($_GET["success"]); ?>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET["error"])): ?>
        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            <?php echo htmlspecialchars($_GET["error"]); ?>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>
    <form
        method="POST"
        action="actions.php"
        id="userForm"
    >

        <div class="card shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex gap-2 flex-wrap">
                    <button
                        type="submit"
                        name="action"
                        value="block"
                        id="blockBtn"
                        class="btn btn-warning"
                        disabled
                        title="Block selected users"
                    >
                        Block
                    </button>
                    <button
                        type="submit"
                        name="action"
                        value="unblock"
                        id="unblockBtn"
                        class="btn btn-success"
                        disabled
                        title="Unblock selected users"
                    >
                        Unblock
                    </button>
                    <button
                        type="submit"
                        name="action"
                        value="delete"
                        id="deleteBtn"
                        class="btn btn-danger"
                        disabled
                        title="Delete selected users"
                    >
                        Delete
                    </button>
                    <button
                        type="submit"
                        name="action"
                        value="delete_unverified"
                        id="deleteUnverifiedBtn"
                        class="btn btn-outline-danger"
                        title="Delete all unverified users"
                    >
                        Delete Unverified
                    </button>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-dark">
                        <tr>
                            <th
                                class="text-center"
                                style="width: 50px;"
                            >

                                <input
                                    type="checkbox"
                                    id="selectAll"
                                    class="form-check-input"
                                    title="Select or deselect all users"
                                >

                            </th>
                            <th>Name</th>

                            <th>Email</th>

                            <th>Last Login</th>

                            <th>Status</th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($user = $result->fetch_assoc()): ?>
                                <tr>

                                    <td class="text-center">

                                        <input
                                            type="checkbox"
                                            name="user_ids[]"
                                            class="form-check-input user-checkbox"
                                            value="<?php echo $user["id"]; ?>"
                                            title="Select this user"
                                        >

                                    </td>

                                    <td>

                                        <?php
                                        echo htmlspecialchars($user["name"]);
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo htmlspecialchars($user["email"]);
                                        ?>

                                    </td>

                                    <td>

                                        <?php

                                        if ($user["last_login"] !== null) {

                                            echo htmlspecialchars(
                                                $user["last_login"]
                                            );

                                        } else {

                                            echo "Never";

                                        }

                                        ?>

                                    </td>

                                    <td>

                                        <?php if ($user["status"] === "active"): ?>

                                            <span class="badge text-bg-success">
                                                Active
                                            </span>


                                        <?php elseif ($user["status"] === "blocked"): ?>

                                            <span class="badge text-bg-danger">
                                                Blocked
                                            </span>


                                        <?php else: ?>

                                            <span class="badge text-bg-secondary">
                                                Unverified
                                            </span>

                                        <?php endif; ?>


                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center py-4 text-muted"
                                >
                                    No users found.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    </form>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>
function getUniqIdValue() {

    const selectedIds = [];

    document
        .querySelectorAll(".user-checkbox:checked")
        .forEach(function (checkbox) {

            selectedIds.push(checkbox.value);

        });

    return selectedIds;
}

function updateToolbar() {

    const selectedIds = getUniqIdValue();

    const hasSelection = selectedIds.length > 0;

    document.getElementById("blockBtn").disabled =
        !hasSelection;

    document.getElementById("unblockBtn").disabled =
        !hasSelection;

    document.getElementById("deleteBtn").disabled =
        !hasSelection;
}
const selectAll =
    document.getElementById("selectAll");
const userCheckboxes =
    document.querySelectorAll(".user-checkbox");

selectAll.addEventListener("change", function () {

    userCheckboxes.forEach(function (checkbox) {

        checkbox.checked = selectAll.checked;

    });

    updateToolbar();

});

userCheckboxes.forEach(function (checkbox) {

    checkbox.addEventListener("change", function () {

        updateToolbar();

    });

});
</script>
</body>
</html>