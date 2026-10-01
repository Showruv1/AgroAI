<?php

require_once __DIR__ . "/database.php";

date_default_timezone_set("Asia/Dhaka");

$selectedFarm = $_GET["farm_type"] ?? "crop";

if (!in_array($selectedFarm, ["crop", "fish"], true)) {
    $selectedFarm = "crop";
}

$error = "";
$success = "";
$editData = null;


/* =========================================================
   DELETE TRANSACTION
========================================================= */

if (isset($_GET["delete"])) {

    $deleteId = (int) $_GET["delete"];

    if ($deleteId > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM transactions
             WHERE id = ? AND farm_type = ?"
        );

        $stmt->bind_param(
            "is",
            $deleteId,
            $selectedFarm
        );

        if ($stmt->execute()) {
            $success = "লেনদেনটি সফলভাবে মুছে ফেলা হয়েছে।";
        } else {
            $error = "লেনদেন মুছে ফেলা যায়নি।";
        }

        $stmt->close();
    }
}


/* =========================================================
   ADD / EDIT TRANSACTION
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "add";

    $farmType = $_POST["farm_type"] ?? "crop";
    $date = trim($_POST["transaction_date"] ?? "");
    $type = $_POST["type"] ?? "";
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $amount = trim($_POST["amount"] ?? "");

    if (!in_array($farmType, ["crop", "fish"], true)) {
        $farmType = "crop";
    }

    if (
        empty($date) ||
        !in_array($type, ["income", "expense"], true) ||
        empty($category) ||
        !is_numeric($amount) ||
        (float)$amount <= 0
    ) {

        $error = "সব প্রয়োজনীয় তথ্য সঠিকভাবে পূরণ করুন。";

    } else {

        $amount = (float) $amount;


        /* =========================
           EDIT
        ========================= */

        if ($action === "edit") {

            $editId = (int) ($_POST["edit_id"] ?? 0);

            if ($editId > 0) {

                $sql = "UPDATE transactions
                        SET farm_type = ?,
                            transaction_date = ?,
                            type = ?,
                            category = ?,
                            description = ?,
                            amount = ?
                        WHERE id = ?";

                $stmt = $conn->prepare($sql);

                if ($stmt === false) {

                    $error = "লেনদেন পরিবর্তন করা যাচ্ছে না।";

                } else {

                    $stmt->bind_param(
                        "sssssdi",
                        $farmType,
                        $date,
                        $type,
                        $category,
                        $description,
                        $amount,
                        $editId
                    );

                    if ($stmt->execute()) {
                        $success = "লেনদেন সফলভাবে পরিবর্তন করা হয়েছে।";
                    } else {
                        $error = "লেনদেন পরিবর্তন করা যায়নি।";
                    }

                    $stmt->close();
                }
            }


        /* =========================
           ADD
        ========================= */

        } else {

            $sql = "INSERT INTO transactions
                    (
                        farm_type,
                        transaction_date,
                        type,
                        category,
                        description,
                        amount
                    )
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {

                $error = "লেনদেন যোগ করা যাচ্ছে না।";

            } else {

                $stmt->bind_param(
                    "sssssd",
                    $farmType,
                    $date,
                    $type,
                    $category,
                    $description,
                    $amount
                );

                if ($stmt->execute()) {
                    $success = "লেনদেন সফলভাবে যোগ করা হয়েছে।";
                } else {
                    $error = "লেনদেন যোগ করা যায়নি।";
                }

                $stmt->close();
            }
        }
    }

    $selectedFarm = $farmType;
}


/* =========================================================
   LOAD EDIT DATA
========================================================= */

if (isset($_GET["edit"])) {

    $editId = (int) $_GET["edit"];

    if ($editId > 0) {

        $stmt = $conn->prepare(
            "SELECT *
             FROM transactions
             WHERE id = ? AND farm_type = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "is",
            $editId,
            $selectedFarm
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $editData = $result->fetch_assoc();
        }

        $stmt->close();
    }
}


/* =========================================================
   TODAY SUMMARY
========================================================= */

$todayIncome = 0;
$todayExpense = 0;

$todaySql = "SELECT

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'income'
                            THEN amount
                            ELSE 0
                        END
                    ), 0
                ) AS income,

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'expense'
                            THEN amount
                            ELSE 0
                        END
                    ), 0
                ) AS expense

             FROM transactions

             WHERE farm_type = ?
             AND transaction_date = CURDATE()";

$stmt = $conn->prepare($todaySql);

$stmt->bind_param(
    "s",
    $selectedFarm
);

$stmt->execute();

$todayResult = $stmt->get_result();

if ($row = $todayResult->fetch_assoc()) {

    $todayIncome = (float) $row["income"];
    $todayExpense = (float) $row["expense"];
}

$stmt->close();

$todayProfit = $todayIncome - $todayExpense;


/* =========================================================
   MONTH SUMMARY
========================================================= */

$monthIncome = 0;
$monthExpense = 0;

$monthSql = "SELECT

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'income'
                            THEN amount
                            ELSE 0
                        END
                    ), 0
                ) AS income,

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'expense'
                            THEN amount
                            ELSE 0
                        END
                    ), 0
                ) AS expense

             FROM transactions

             WHERE farm_type = ?
             AND YEAR(transaction_date) = YEAR(CURDATE())
             AND MONTH(transaction_date) = MONTH(CURDATE())";

$stmt = $conn->prepare($monthSql);

$stmt->bind_param(
    "s",
    $selectedFarm
);

$stmt->execute();

$monthResult = $stmt->get_result();

if ($row = $monthResult->fetch_assoc()) {

    $monthIncome = (float) $row["income"];
    $monthExpense = (float) $row["expense"];
}

$stmt->close();

$monthProfit = $monthIncome - $monthExpense;


/* =========================================================
   RECENT TRANSACTIONS
========================================================= */

$recentTransactions = [];

$recentSql = "SELECT *
              FROM transactions
              WHERE farm_type = ?
              ORDER BY transaction_date DESC, id DESC
              LIMIT 10";

$stmt = $conn->prepare($recentSql);

$stmt->bind_param(
    "s",
    $selectedFarm
);

$stmt->execute();

$recentResult = $stmt->get_result();

while ($row = $recentResult->fetch_assoc()) {
    $recentTransactions[] = $row;
}

$stmt->close();


/* =========================================================
   MONTHLY CHART DATA
========================================================= */

$chartLabels = [];
$chartIncome = [];
$chartExpense = [];

$chartSql = "SELECT

                DATE_FORMAT(
                    transaction_date,
                    '%Y-%m'
                ) AS month,

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'income'
                            THEN amount
                            ELSE 0
                        END
                    ), 0
                ) AS income,

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'expense'
                            THEN amount
                            ELSE 0
                        END
                    ), 0
                ) AS expense

             FROM transactions

             WHERE farm_type = ?

             GROUP BY DATE_FORMAT(
                transaction_date,
                '%Y-%m'
             )

             ORDER BY month ASC";

$stmt = $conn->prepare($chartSql);

$stmt->bind_param(
    "s",
    $selectedFarm
);

$stmt->execute();

$chartResult = $stmt->get_result();

while ($row = $chartResult->fetch_assoc()) {

    $chartLabels[] = $row["month"];
    $chartIncome[] = (float) $row["income"];
    $chartExpense[] = (float) $row["expense"];
}

$stmt->close();


/* =========================================================
   DAILY CHART DATA
   CURRENT MONTH
========================================================= */

$dailyLabels = [];
$dailyIncome = [];
$dailyExpense = [];
$dailyProfit = [];

$dailySql = "SELECT

                DATE_FORMAT(
                    transaction_date,
                    '%d %b'
                ) AS day,

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'income'
                            THEN amount
                            ELSE 0
                        END
                    ), 0
                ) AS income,

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'expense'
                            THEN amount
                            ELSE 0
                        END
                    ), 0
                ) AS expense

             FROM transactions

             WHERE farm_type = ?

             AND YEAR(transaction_date) = YEAR(CURDATE())

             AND MONTH(transaction_date) = MONTH(CURDATE())

             GROUP BY transaction_date

             ORDER BY transaction_date ASC";

$stmt = $conn->prepare($dailySql);

$stmt->bind_param(
    "s",
    $selectedFarm
);

$stmt->execute();

$dailyResult = $stmt->get_result();

while ($row = $dailyResult->fetch_assoc()) {

    $income = (float) $row["income"];
    $expense = (float) $row["expense"];

    $dailyLabels[] = $row["day"];
    $dailyIncome[] = $income;
    $dailyExpense[] = $expense;
    $dailyProfit[] = $income - $expense;
}

$stmt->close();

?>

<!DOCTYPE html>

<html lang="bn">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        AgroAI - আয় ও ব্যয়
    </title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 20px;

            font-family:
                "Noto Sans Bengali",
                "Segoe UI",
                Arial,
                sans-serif;

            background: #f0fdf4;

            color: #1f2937;
        }


        .container {

            max-width: 1100px;

            margin: auto;
        }


        .header {

            background: #166534;

            color: white;

            padding: 25px;

            border-radius: 14px;

            margin-bottom: 20px;

            text-align: center;
        }


        .header h1 {

            margin: 0 0 8px;

            font-size: 28px;
        }


        .header p {

            margin: 0;

            font-size: 16px;
        }


        .farm-selector {

            display: flex;

            gap: 12px;

            margin-bottom: 20px;
        }


        .farm-selector a {

            flex: 1;

            text-align: center;

            padding: 14px;

            border-radius: 10px;

            text-decoration: none;

            font-weight: bold;

            background: white;

            color: #166534;

            border: 2px solid #bbf7d0;
        }


        .farm-selector a.active {

            background: #16a34a;

            color: white;

            border-color: #16a34a;
        }


        .message {

            padding: 14px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-weight: bold;
        }


        .success {

            background: #dcfce7;

            color: #166534;
        }


        .error {

            background: #fee2e2;

            color: #991b1b;
        }


        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 25px;
        }


        .summary-card {

            background: white;

            padding: 20px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.06);

            text-align: center;
        }


        .summary-card h3 {

            margin: 0 0 10px;

            font-size: 17px;

            color: #374151;
        }


        .summary-card .amount {

            font-size: 25px;

            font-weight: bold;

            color: #166534;
        }


        .expense-card .amount {

            color: #dc2626;
        }


        .profit-card .amount {

            color: #2563eb;
        }


        .form-section {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.06);
        }


        .form-section h2 {

            margin-top: 0;

            color: #166534;
        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;
        }


        .form-group {

            display: flex;

            flex-direction: column;

            gap: 6px;
        }


        .form-group.full {

            grid-column: 1 / -1;
        }


        label {

            font-weight: bold;

            color: #374151;
        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 12px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            font-size: 15px;

            font-family: inherit;
        }


        textarea {

            resize: vertical;

            min-height: 80px;
        }


        input:focus,
        select:focus,
        textarea:focus {

            outline: none;

            border-color: #16a34a;

            box-shadow:
                0 0 0 3px rgba(22, 163, 74, 0.12);
        }


        .submit-btn {

            width: 100%;

            margin-top: 18px;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #16a34a;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }


        .submit-btn:hover {

            background: #15803d;
        }


        .cancel-btn {

            display: block;

            text-align: center;

            margin-top: 10px;

            padding: 12px;

            border-radius: 8px;

            background: #f3f4f6;

            color: #374151;

            text-decoration: none;
        }


        .chart-section {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.06);
        }


        .chart-section h2 {

            margin-top: 0;

            margin-bottom: 20px;

            color: #166534;
        }


        .chart-description {

            color: #6b7280;

            margin-top: -10px;

            margin-bottom: 20px;

            font-size: 14px;
        }


        .chart-container {

            position: relative;

            width: 100%;

            height: 350px;
        }


        .table-section {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.06);

            overflow-x: auto;
        }


        .table-section h2 {

            margin-top: 0;

            color: #166534;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 750px;
        }


        th {

            background: #166534;

            color: white;

            padding: 12px;

            text-align: left;
        }


        td {

            padding: 12px;

            border-bottom:
                1px solid #e5e7eb;
        }


        tr:hover {

            background: #f9fafb;
        }


        .income-text {

            color: #15803d;

            font-weight: bold;
        }


        .expense-text {

            color: #dc2626;

            font-weight: bold;
        }


        .edit-btn,
        .delete-btn {

            display: inline-block;

            padding: 7px 10px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 13px;

            margin: 2px;
        }


        .edit-btn {

            background: #dbeafe;

            color: #1d4ed8;
        }


        .delete-btn {

            background: #fee2e2;

            color: #b91c1c;
        }


        .back-btn {

            display: inline-block;

            padding: 12px 18px;

            background: #374151;

            color: white;

            text-decoration: none;

            border-radius: 8px;

            margin-top: 5px;
        }


        @media (max-width: 768px) {

            body {
                padding: 10px;
            }


            .summary-grid {

                grid-template-columns: 1fr;
            }


            .form-grid {

                grid-template-columns: 1fr;
            }


            .farm-selector {

                flex-direction: column;
            }


            .header h1 {

                font-size: 23px;
            }


            .chart-container {

                height: 280px;
            }
        }

    </style>

</head>


<body>

<div class="container">


    <!-- HEADER -->

    <header class="header">

        <h1>
            💰 আয়–ব্যয় হিসাব
        </h1>

        <p>

            <?= $selectedFarm === "crop"
                ? "🌾 ফসল চাষ"
                : "🐟 মাছ চাষ"
            ?>

        </p>

    </header>


    <!-- FARM SELECTOR -->

    <div class="farm-selector">

        <a
            href="?farm_type=crop"
            class="<?= $selectedFarm === "crop" ? "active" : "" ?>"
        >
            🌾 ফসল চাষ
        </a>


        <a
            href="?farm_type=fish"
            class="<?= $selectedFarm === "fish" ? "active" : "" ?>"
        >
            🐟 মাছ চাষ
        </a>

    </div>


    <!-- MESSAGES -->

    <?php if (!empty($success)): ?>

        <div class="message success">

            <?= htmlspecialchars(
                $success,
                ENT_QUOTES,
                "UTF-8"
            ) ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="message error">

            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                "UTF-8"
            ) ?>

        </div>

    <?php endif; ?>


    <!-- SUMMARY -->

    <div class="summary-grid">


        <div class="summary-card">

            <h3>
                📅 আজকের মোট আয়
            </h3>

            <div class="amount">

                ৳<?= number_format(
                    $todayIncome,
                    2
                ) ?>

            </div>

        </div>


        <div class="summary-card expense-card">

            <h3>
                📅 আজকের মোট ব্যয়
            </h3>

            <div class="amount">

                ৳<?= number_format(
                    $todayExpense,
                    2
                ) ?>

            </div>

        </div>


        <div class="summary-card profit-card">

            <h3>
                💰 আজকের লাভ/ক্ষতি
            </h3>

            <div class="amount">

                ৳<?= number_format(
                    $todayProfit,
                    2
                ) ?>

            </div>

        </div>


        <div class="summary-card">

            <h3>
                📆 চলতি মাসের আয়
            </h3>

            <div class="amount">

                ৳<?= number_format(
                    $monthIncome,
                    2
                ) ?>

            </div>

        </div>


        <div class="summary-card expense-card">

            <h3>
                📆 চলতি মাসের ব্যয়
            </h3>

            <div class="amount">

                ৳<?= number_format(
                    $monthExpense,
                    2
                ) ?>

            </div>

        </div>


        <div class="summary-card profit-card">

            <h3>
                📊 চলতি মাসের লাভ/ক্ষতি
            </h3>

            <div class="amount">

                ৳<?= number_format(
                    $monthProfit,
                    2
                ) ?>

            </div>

        </div>

    </div>


    <!-- ADD / EDIT FORM -->

    <section class="form-section">

        <h2>

            <?= $editData
                ? "✏️ লেনদেন পরিবর্তন করুন"
                : "➕ নতুন লেনদেন যোগ করুন"
            ?>

        </h2>


        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="<?= $editData ? "edit" : "add" ?>"
            >


            <?php if ($editData): ?>

                <input
                    type="hidden"
                    name="edit_id"
                    value="<?= (int)$editData["id"] ?>"
                >

            <?php endif; ?>


            <input
                type="hidden"
                name="farm_type"
                value="<?= htmlspecialchars(
                    $selectedFarm,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
            >


            <div class="form-grid">


                <div class="form-group">

                    <label>
                        তারিখ
                    </label>

                    <input
                        type="date"
                        name="transaction_date"
                        value="<?= htmlspecialchars(
                            $editData["transaction_date"]
                            ?? date("Y-m-d"),
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        ধরন
                    </label>

                    <select
                        name="type"
                        required
                    >

                        <option
                            value="income"
                            <?= (
                                ($editData["type"] ?? "")
                                === "income"
                            )
                            ? "selected"
                            : ""
                            ?>
                        >
                            💰 আয়
                        </option>


                        <option
                            value="expense"
                            <?= (
                                ($editData["type"] ?? "")
                                === "expense"
                            )
                            ? "selected"
                            : ""
                            ?>
                        >
                            💸 ব্যয়
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        ক্যাটাগরি
                    </label>

                    <input
                        type="text"
                        name="category"
                        placeholder="যেমন: ফসল বিক্রি / খাবার / শ্রমিক"
                        value="<?= htmlspecialchars(
                            $editData["category"]
                            ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        পরিমাণ (টাকা)
                    </label>

                    <input
                        type="number"
                        name="amount"
                        step="0.01"
                        min="0.01"
                        placeholder="যেমন: 5000"
                        value="<?= htmlspecialchars(
                            $editData["amount"]
                            ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                        required
                    >

                </div>


                <div class="form-group full">

                    <label>
                        বিস্তারিত
                    </label>

                    <textarea
                        name="description"
                        placeholder="লেনদেন সম্পর্কে সংক্ষিপ্ত তথ্য লিখুন"
                    ><?= htmlspecialchars(
                        $editData["description"]
                        ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?></textarea>

                </div>

            </div>


            <button
                type="submit"
                class="submit-btn"
            >

                <?= $editData
                    ? "💾 পরিবর্তন সংরক্ষণ করুন"
                    : "➕ লেনদেন যোগ করুন"
                ?>

            </button>


            <?php if ($editData): ?>

                <a
                    href="?farm_type=<?= $selectedFarm ?>"
                    class="cancel-btn"
                >
                    ❌ পরিবর্তন বাতিল করুন
                </a>

            <?php endif; ?>

        </form>

    </section>


    <!-- MONTHLY CHART -->

    <section class="chart-section">

        <h2>
            📊 মাসভিত্তিক আয়–ব্যয়
        </h2>

        <p class="chart-description">
            প্রতিটি মাসে মোট কত আয় এবং কত ব্যয় হয়েছে তা দেখুন।
        </p>


        <?php if (count($chartLabels) > 0): ?>

            <div class="chart-container">

                <canvas
                    id="incomeExpenseChart"
                ></canvas>

            </div>

        <?php else: ?>

            <p>
                এখনো পর্যাপ্ত লেনদেনের তথ্য নেই।
            </p>

        <?php endif; ?>

    </section>


    <!-- DAILY CHART -->

    <section class="chart-section">

        <h2>
            📈 দৈনিক আয়–ব্যয় ও লাভ/ক্ষতি
        </h2>

        <p class="chart-description">
            চলতি মাসে প্রতিদিনের আয়, ব্যয় এবং লাভ/ক্ষতির পরিবর্তন দেখুন।
        </p>


        <?php if (count($dailyLabels) > 0): ?>

            <div class="chart-container">

                <canvas
                    id="dailyProfitChart"
                ></canvas>

            </div>

        <?php else: ?>

            <p>
                চলতি মাসের কোনো লেনদেনের তথ্য পাওয়া যায়নি।
            </p>

        <?php endif; ?>

    </section>


    <!-- RECENT TRANSACTIONS -->

    <section class="table-section">

        <h2>
            📋 সাম্প্রতিক লেনদেন
        </h2>


        <?php if (count($recentTransactions) > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>তারিখ</th>

                        <th>ধরন</th>

                        <th>ক্যাটাগরি</th>

                        <th>বিস্তারিত</th>

                        <th>পরিমাণ</th>

                        <th>কাজ</th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach (
                    $recentTransactions
                    as $row
                ): ?>

                    <tr>

                        <td>

                            <?= htmlspecialchars(
                                $row["transaction_date"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </td>


                        <td>

                            <?php if (
                                $row["type"]
                                === "income"
                            ): ?>

                                <span class="income-text">
                                    💰 আয়
                                </span>

                            <?php else: ?>

                                <span class="expense-text">
                                    💸 ব্যয়
                                </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row["category"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row["description"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </td>


                        <td>

                            ৳<?= number_format(
                                (float)$row["amount"],
                                2
                            ) ?>

                        </td>


                        <td>

                            <a
                                class="edit-btn"
                                href="?farm_type=<?= $selectedFarm ?>&edit=<?= (int)$row["id"] ?>"
                            >
                                ✏️ পরিবর্তন
                            </a>


                            <a
                                class="delete-btn"
                                href="?farm_type=<?= $selectedFarm ?>&delete=<?= (int)$row["id"] ?>"
                                onclick="return confirm('আপনি কি এই লেনদেনটি মুছে ফেলতে চান?');"
                            >
                                🗑️ মুছে ফেলুন
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <p>
                এখনো কোনো লেনদেন যোগ করা হয়নি।
            </p>

        <?php endif; ?>

    </section>


    <!-- BACK -->

    <a
        href="../index.html"
        class="back-btn"
    >
        ← ড্যাশবোর্ডে ফিরে যান
    </a>


</div>


<!-- MONTHLY CHART SCRIPT -->

<?php if (count($chartLabels) > 0): ?>

<script>

const chartLabels =
    <?= json_encode(
        $chartLabels,
        JSON_UNESCAPED_UNICODE
    ) ?>;

const chartIncome =
    <?= json_encode(
        $chartIncome
    ) ?>;

const chartExpense =
    <?= json_encode(
        $chartExpense
    ) ?>;


const monthlyCtx =
    document.getElementById(
        "incomeExpenseChart"
    );


new Chart(monthlyCtx, {

    type: "bar",

    data: {

        labels: chartLabels,

        datasets: [

            {
                label: "আয়",

                data: chartIncome,

                borderWidth: 1
            },

            {
                label: "ব্যয়",

                data: chartExpense,

                borderWidth: 1
            }

        ]

    },


    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {

                display: true,

                position: "top"
            }

        },


        scales: {

            y: {

                beginAtZero: true,

                title: {

                    display: true,

                    text: "টাকা"
                }

            },


            x: {

                title: {

                    display: true,

                    text: "মাস"
                }

            }

        }

    }

});

</script>

<?php endif; ?>


<!-- DAILY CHART SCRIPT -->

<?php if (count($dailyLabels) > 0): ?>

<script>

const dailyLabels =
    <?= json_encode(
        $dailyLabels,
        JSON_UNESCAPED_UNICODE
    ) ?>;

const dailyIncome =
    <?= json_encode(
        $dailyIncome
    ) ?>;

const dailyExpense =
    <?= json_encode(
        $dailyExpense
    ) ?>;

const dailyProfit =
    <?= json_encode(
        $dailyProfit
    ) ?>;


const dailyCtx =
    document.getElementById(
        "dailyProfitChart"
    );


new Chart(dailyCtx, {

    type: "line",

    data: {

        labels: dailyLabels,

        datasets: [

            {
                label: "আয়",

                data: dailyIncome,

                borderWidth: 3,

                tension: 0.3,

                fill: false
            },

            {
                label: "ব্যয়",

                data: dailyExpense,

                borderWidth: 3,

                tension: 0.3,

                fill: false
            },

            {
                label: "লাভ/ক্ষতি",

                data: dailyProfit,

                borderWidth: 3,

                tension: 0.3,

                fill: false
            }

        ]

    },


    options: {

        responsive: true,

        maintainAspectRatio: false,

        interaction: {

            mode: "index",

            intersect: false
        },


        plugins: {

            legend: {

                display: true,

                position: "top"
            }

        },


        scales: {

            y: {

                title: {

                    display: true,

                    text: "টাকা"
                }

            },


            x: {

                title: {

                    display: true,

                    text: "তারিখ"
                }

            }

        }

    }

});

</script>

<?php endif; ?>


</body>

</html>