function showMessage(module) {

    if (module === "soil") {
        window.location.href = "backend/soil_ph.php";
        return;
    }

    if (module === "water") {
        window.location.href = "backend/water_ph.php";
        return;
    }

    if (module === "weather") {
        window.location.href = "backend/weather.php";
        return;
    }

    if (module === "crop-finance" || module === "fish-finance") {
        const category = module === "crop-finance" ? "crop" : "fish";
        window.location.href = `backend/income_expense.php?category=${category}`;
        return;
    }

    const messageBox = document.getElementById("message-box");

    const messages = {
        weather: "🌦️ Weather module খুব শীঘ্রই তৈরি করা হবে।",
        soil: "🧪 Soil pH Analysis module খুব শীঘ্রই তৈরি করা হবে।",
        "crop-finance": "💰 Crop Income-Expense module খুব শীঘ্রই তৈরি করা হবে।",
        water: "💧 Water pH Analysis module খুব শীঘ্রই তৈরি করা হবে।",
        "fish-finance": "💰 Fish Income-Expense module খুব শীঘ্রই তৈরি করা হবে।"
    };

    messageBox.innerHTML = `<p>${messages[module]}</p>`;
}