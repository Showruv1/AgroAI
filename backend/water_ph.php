<?php

require_once __DIR__ . "/database.php";

$phValue = "";
$result = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phValue = trim($_POST["ph"] ?? "");

    if (!is_numeric($phValue)) {
        $error = "সঠিক pH মান দিন।";
    } else {
       $ph = (float) $phValue;

        if ($ph < 0 || $ph > 14) {
            $error = "pH মান ০ থেকে ১৪-এর মধ্যে হতে হবে।";
        } else {
            $sql = "SELECT * FROM water_ph_ranges
                    WHERE ? >= min_ph
                    AND (max_ph IS NULL OR ? <= max_ph)
                    LIMIT 1";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $error = "পানির pH তথ্য লোড করা যাচ্ছে না। ডাটাবেস সেটআপ পরীক্ষা করুন।";
            } else {
                $stmt->bind_param("dd", $ph, $ph);
                $stmt->execute();
                $queryResult = $stmt->get_result();

                if ($queryResult->num_rows > 0) {
                    $data = $queryResult->fetch_assoc();
                    $result = [
                        "condition" => $data["condition_bn"],
                        "explanation" => $data["explanation_bn"],
                        "advice" => $data["advice_bn"],
                        "warning" => $data["warning_bn"]
                    ];
                } else {
                    $error = "এই pH মানের জন্য কোনো তথ্য পাওয়া যায়নি।";
                }

                $stmt->close();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পানির pH পরীক্ষা | AgroAI</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 32px 16px;
            background: #f4f8f3;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }
        main {
            width: min(100%, 620px);
            margin: 0 auto;
            padding: 28px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        h1 { margin: 0 0 10px; color: #166534; }
        p { line-height: 1.6; }
        label { display: block; margin: 24px 0 8px; font-weight: 700; }
        input, button {
            width: 100%;
            min-height: 48px;
            padding: 12px;
            font: inherit;
        }
        input { border: 1px solid #94a3b8; border-radius: 6px; }
        button {
            margin-top: 14px;
            border: 0;
            border-radius: 6px;
            background: #16a34a;
            color: #fff;
            cursor: pointer;
        }
        button:hover { background: #15803d; }
        .notice { margin-top: 20px; padding: 16px; border-radius: 6px; }
        .error { background: #fef2f2; color: #991b1b; }
        .result { background: #f0fdf4; color: #166534; }
        .back { display: inline-block; margin-top: 20px; color: #166534; }
    </style>
</head>
<body>
    <main>
        <h1>পানির pH পরীক্ষা</h1>
        <p>আপনার পরীক্ষার কিট বা ল্যাব থেকে পাওয়া পানির pH মান লিখুন। মাছের প্রজাতি ও পরিবেশ অনুযায়ী উপযুক্ত মান ভিন্ন হতে পারে।</p>

        <form method="post">
            <label for="ph">পানির pH (০-১৪)</label>
            <input
                id="ph"
                name="ph"
                type="number"
                min="0"
                max="14"
                step="0.1"
                value="<?= htmlspecialchars($phValue, ENT_QUOTES, "UTF-8") ?>"
                required
            >
            <button type="submit">পরীক্ষার ফল দেখুন</button>
        </form>

        <?php if ($error !== ""): ?>
            <p class="notice error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>

        <?php if ($result !== null): ?>
            <section class="notice result" aria-live="polite">
                <h2>ফলাফল: <?= htmlspecialchars($result["condition"], ENT_QUOTES, "UTF-8") ?></h2>
                <p><strong>আপনার দেওয়া pH:</strong> <?= htmlspecialchars($phValue, ENT_QUOTES, "UTF-8") ?></p>
                <p><strong>ব্যাখ্যা:</strong><br><?= htmlspecialchars($result["explanation"], ENT_QUOTES, "UTF-8") ?></p>
                <p><strong>পরামর্শ:</strong><br><?= htmlspecialchars($result["advice"], ENT_QUOTES, "UTF-8") ?></p>
                <p><strong>সতর্কতা:</strong><br><?= htmlspecialchars($result["warning"], ENT_QUOTES, "UTF-8") ?></p>
            </section>
        <?php endif; ?>

        <a class="back" href="../index.html">AgroAI-তে ফিরে যান</a>
    </main>
</body>
</html>
