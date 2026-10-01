<!DOCTYPE html>
<html lang="bn">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>আবহাওয়া | AgroAI</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 28px 16px;
            background: #f4f8f3;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        main {
            width: min(100%, 920px);
            margin: 0 auto;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        h1 {
            margin: 0;
            color: #166534;
        }

        h2 {
            margin-top: 0;
        }

        h3 {
            color: #166534;
        }

        p {
            line-height: 1.6;
        }

        .back {
            color: #166534;
        }

        .panel {
            margin-top: 22px;
            padding: 24px;
            background: #fff;
            border-radius: 8px;
        }

        .search {
            display: flex;
            gap: 10px;
        }

        input,
        button {
            min-height: 46px;
            padding: 10px 12px;
            font: inherit;
        }

        input {
            flex: 1;
            min-width: 0;
            border: 1px solid #94a3b8;
            border-radius: 6px;
        }

        button {
            border: 0;
            border-radius: 6px;
            background: #166534;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background: #14532d;
        }

        button:disabled {
            cursor: wait;
            opacity: 0.65;
        }

        .location-button {
            margin-top: 12px;
            background: #e2e8f0;
            color: #1f2937;
        }

        .location-button:hover {
            background: #cbd5e1;
        }

        .status {
            min-height: 24px;
            color: #475569;
        }

        .status.error {
            color: #b91c1c;
        }

        .current {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) 2fr;
            gap: 24px;
            align-items: center;
        }

        .temperature {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .weather-icon {
            font-size: 48px;
            line-height: 1;
        }

        .temperature strong {
            font-size: 42px;
            font-weight: 600;
        }

        .condition {
            margin: 4px 0 0;
            color: #475569;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .metric {
            padding: 14px;
            background: #f4f8f3;
            border-radius: 6px;
        }

        .metric span {
            display: block;
            color: #64748b;
        }

        .metric strong {
            display: block;
            margin-top: 5px;
        }

        .forecast {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
        }

        .forecast-day {
            padding: 14px 10px;
            background: #f4f8f3;
            border-radius: 6px;
            text-align: center;
        }

        .forecast-day p {
            margin: 8px 0;
        }

        .forecast-day .weather-icon {
            display: block;
            margin: 10px 0;
            font-size: 30px;
        }

        .forecast-day small {
            color: #64748b;
        }

        .note {
            color: #64748b;
            font-size: 14px;
        }

        /* AgroAI Analysis */

        .agro-analysis {
            border-left: 5px solid #166534;
        }

        .agro-alert {
            padding: 16px;
            margin-bottom: 20px;
            background: #fef3c7;
            border-radius: 8px;
            line-height: 1.7;
        }

        .agro-analysis ul {
            padding-left: 22px;
        }

        .agro-analysis li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        [hidden] {
            display: none !important;
        }

        @media (max-width: 650px) {

            .current {
                grid-template-columns: 1fr;
            }

            .forecast {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .search {
                flex-wrap: wrap;
            }

            .search input {
                flex-basis: 100%;
            }

            .search button {
                flex: 1;
            }

            .panel {
                padding: 18px;
            }

        }

    </style>

</head>

<body>

    <main>

        <header>

            <h1>আবহাওয়া</h1>

            <a class="back" href="../index.html">
                AgroAI-তে ফিরে যান
            </a>

        </header>


        <!-- Location Search -->

        <section class="panel">

            <h2>এলাকার আবহাওয়া</h2>

            <form id="location-form" class="search">

                <input
                    id="location-input"
                    type="search"
                    value="ঢাকা"
                    placeholder="শহর বা এলাকার নাম"
                    aria-label="শহর বা এলাকার নাম"
                    required
                >

                <button id="search-button" type="submit">
                    আবহাওয়া দেখুন
                </button>

            </form>


            <button
                id="location-button"
                class="location-button"
                type="button"
            >
                📍 আমার অবস্থান ব্যবহার করুন
            </button>


            <p
                id="status"
                class="status"
                role="status"
                aria-live="polite"
            ></p>

        </section>


        <!-- Weather Results -->

        <div id="weather-results" hidden>


            <!-- Current Weather -->

            <section class="panel">

                <h2 id="place-name"></h2>

                <div class="current">

                    <div>

                        <div class="temperature">

                            <span
                                id="current-icon"
                                class="weather-icon"
                                aria-hidden="true"
                            ></span>

                            <strong>
                                <span id="current-temperature"></span>°C
                            </strong>

                        </div>

                        <p
                            id="current-condition"
                            class="condition"
                        ></p>

                    </div>


                    <div class="metrics">

                        <div class="metric">
                            <span>অনুভূত তাপমাত্রা</span>
                            <strong id="feels-like"></strong>
                        </div>

                        <div class="metric">
                            <span>আর্দ্রতা</span>
                            <strong id="humidity"></strong>
                        </div>

                        <div class="metric">
                            <span>বাতাসের গতি</span>
                            <strong id="wind-speed"></strong>
                        </div>

                        <div class="metric">
                            <span>বৃষ্টিপাত</span>
                            <strong id="precipitation"></strong>
                        </div>

                    </div>

                </div>

            </section>


            <!-- 5 Day Forecast -->

            <section class="panel">

                <h2>আগামী ৫ দিনের পূর্বাভাস</h2>

                <div
                    id="forecast"
                    class="forecast"
                ></div>

                <p class="note">
                    পূর্বাভাস Open-Meteo থেকে নেওয়া।
                    মাঠের কাজের আগে স্থানীয় আবহাওয়ার আপডেট মিলিয়ে নিন।
                </p>

            </section>


            <!-- AgroAI Analysis -->

            <section
                class="panel agro-analysis"
                id="agro-analysis"
            >

                <h2>🌾 AgroAI কৃষি বিশ্লেষণ</h2>

                <div
                    id="weather-alert"
                    class="agro-alert"
                >
                    আবহাওয়ার তথ্য বিশ্লেষণ করা হচ্ছে...
                </div>


                <h3>👨‍🌾 কৃষকের করণীয়</h3>

                <ul id="farmer-actions">

                    <li>
                        আবহাওয়ার তথ্য বিশ্লেষণ করা হচ্ছে...
                    </li>

                </ul>

            </section>


        </div>

    </main>


    <script>

        const form = document.getElementById("location-form");

        const locationInput =
            document.getElementById("location-input");

        const searchButton =
            document.getElementById("search-button");

        const locationButton =
            document.getElementById("location-button");

        const status =
            document.getElementById("status");

        const results =
            document.getElementById("weather-results");


        /*
         * Weather Code → Bangla Description
         */

        function weatherDetails(code) {

            if (code === 0) {
                return {
                    label: "পরিষ্কার আকাশ",
                    icon: "☀️"
                };
            }

            if ([1, 2].includes(code)) {
                return {
                    label: "আংশিক মেঘলা",
                    icon: "🌤️"
                };
            }

            if (code === 3) {
                return {
                    label: "মেঘলা",
                    icon: "☁️"
                };
            }

            if ([45, 48].includes(code)) {
                return {
                    label: "কুয়াশা",
                    icon: "🌫️"
                };
            }

            if ([51, 53, 55, 56, 57].includes(code)) {
                return {
                    label: "গুঁড়ি গুঁড়ি বৃষ্টি",
                    icon: "🌦️"
                };
            }

            if ([61, 63, 65, 66, 67, 80, 81, 82].includes(code)) {
                return {
                    label: "বৃষ্টি",
                    icon: "🌧️"
                };
            }

            if ([71, 73, 75, 77, 85, 86].includes(code)) {
                return {
                    label: "তুষারপাত",
                    icon: "🌨️"
                };
            }

            if ([95, 96, 99].includes(code)) {
                return {
                    label: "বজ্রসহ ঝড়",
                    icon: "⛈️"
                };
            }

            return {
                label: "আবহাওয়ার তথ্য পাওয়া যায়নি",
                icon: "🌡️"
            };

        }


        /*
         * Number Formatting
         */

        function formatNumber(value) {

            return new Intl.NumberFormat(
                "bn-BD",
                {
                    maximumFractionDigits: 1
                }
            ).format(value);

        }


        /*
         * Status Message
         */

        function setStatus(message, isError = false) {

            status.textContent = message;

            status.classList.toggle(
                "error",
                isError
            );

        }


        /*
         * AgroAI Weather Analysis
         */

        function generateAgroAdvice(current, daily) {

            const alerts = [];

            const actions = [];


            const temperature =
                Number(current.temperature_2m);

            const humidity =
                Number(current.relative_humidity_2m);

            const precipitation =
                Number(current.precipitation);


            const rainProbabilities =
                Array.isArray(daily.precipitation_probability_max)
                    ? daily.precipitation_probability_max
                    : [];


            const rainProbability =
                rainProbabilities.length > 0
                    ? Math.max(...rainProbabilities)
                    : 0;


            /*
             * Rain Analysis
             */

            if (
                rainProbability >= 70 ||
                precipitation > 5
            ) {

                alerts.push(
                    "🌧️ আগামী কয়েক দিনে বৃষ্টির সম্ভাবনা বেশি।"
                );

                actions.push(
                    "সেচ দেওয়ার আগে বৃষ্টির পূর্বাভাস বিবেচনা করুন।"
                );

                actions.push(
                    "জমিতে পানি জমছে কি না নিয়মিত পর্যবেক্ষণ করুন।"
                );

            }


            /*
             * High Temperature Analysis
             */

            if (temperature >= 35) {

                alerts.push(
                    "🌡️ বর্তমানে তাপমাত্রা বেশি।"
                );

                actions.push(
                    "মাটির আর্দ্রতা নিয়মিত পর্যবেক্ষণ করুন।"
                );

                actions.push(
                    "প্রয়োজন অনুযায়ী সেচের পরিকল্পনা করুন।"
                );

            }


            /*
             * High Humidity Analysis
             */

            if (humidity >= 80) {

                alerts.push(
                    "💧 বাতাসে আর্দ্রতা বেশি।"
                );

                actions.push(
                    "ফসলে অতিরিক্ত আর্দ্রতার কারণে রোগের পরিবেশ তৈরি হচ্ছে কি না পর্যবেক্ষণ করুন।"
                );

            }


            /*
             * Storm / Severe Weather
             */

            const currentWeatherCode =
                Number(current.weather_code);

            if (
                [95, 96, 99].includes(currentWeatherCode)
            ) {

                alerts.push(
                    "⛈️ বজ্রসহ ঝড়ের সম্ভাবনা রয়েছে।"
                );

                actions.push(
                    "বজ্রঝড়ের সময় মাঠে কাজ না করে নিরাপদ স্থানে থাকুন।"
                );

            }


            /*
             * No Major Alert
             */

            if (alerts.length === 0) {

                alerts.push(
                    "☀️ বর্তমানে বড় ধরনের আবহাওয়া সতর্কতা পাওয়া যায়নি।"
                );

                actions.push(
                    "নিয়মিত জমি ও ফসল পর্যবেক্ষণ করুন।"
                );

                actions.push(
                    "আগামী কয়েক দিনের আবহাওয়ার পূর্বাভাস দেখে কৃষিকাজের পরিকল্পনা করুন।"
                );

            }


            /*
             * Update Alert Box
             */

            const alertBox =
                document.getElementById("weather-alert");


            if (alertBox) {

                alertBox.innerHTML =
                    alerts.join("<br>");

            }


            /*
             * Update Farmer Actions
             */

            const actionList =
                document.getElementById("farmer-actions");


            if (actionList) {

                actionList.replaceChildren();


                actions.forEach(action => {

                    const li =
                        document.createElement("li");

                    li.textContent = action;

                    actionList.appendChild(li);

                });

            }

        }


        /*
         * Load Weather Forecast
         */

        async function loadForecast(
            latitude,
            longitude,
            placeName
        ) {

            const params = new URLSearchParams({

                latitude: latitude,

                longitude: longitude,

                current:
                    "temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m",

                daily:
                    "weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max",

                timezone: "auto",

                forecast_days: "5"

            });


            const response = await fetch(
                `https://api.open-meteo.com/v1/forecast?${params}`
            );


            if (!response.ok) {

                throw new Error(
                    "আবহাওয়ার তথ্য আনা যায়নি। কিছুক্ষণ পর আবার চেষ্টা করুন।"
                );

            }


            const data = await response.json();


            if (!data.current || !data.daily) {

                throw new Error(
                    "আবহাওয়ার তথ্য সঠিকভাবে পাওয়া যায়নি।"
                );

            }


            const current =
                data.current;


            const details =
                weatherDetails(
                    current.weather_code
                );


            /*
             * AgroAI Analysis
             */

            generateAgroAdvice(
                current,
                data.daily
            );


            /*
             * Current Weather UI
             */

            document.getElementById(
                "place-name"
            ).textContent = placeName;


            document.getElementById(
                "current-icon"
            ).textContent = details.icon;


            document.getElementById(
                "current-temperature"
            ).textContent =
                formatNumber(
                    current.temperature_2m
                );


            document.getElementById(
                "current-condition"
            ).textContent =
                details.label;


            document.getElementById(
                "feels-like"
            ).textContent =
                `${formatNumber(
                    current.apparent_temperature
                )}°C`;


            document.getElementById(
                "humidity"
            ).textContent =
                `${formatNumber(
                    current.relative_humidity_2m
                )}%`;


            document.getElementById(
                "wind-speed"
            ).textContent =
                `${formatNumber(
                    current.wind_speed_10m
                )} কিমি/ঘণ্টা`;


            document.getElementById(
                "precipitation"
            ).textContent =
                `${formatNumber(
                    current.precipitation
                )} মিমি`;


            /*
             * Forecast UI
             */

            const forecast =
                document.getElementById(
                    "forecast"
                );


            forecast.replaceChildren();


            data.daily.time.forEach(
                (date, index) => {

                    const day =
                        document.createElement(
                            "article"
                        );


                    day.className =
                        "forecast-day";


                    const dateElement =
                        document.createElement(
                            "strong"
                        );


                    dateElement.textContent =
                        new Date(
                            `${date}T12:00:00`
                        ).toLocaleDateString(
                            "bn-BD",
                            {
                                weekday: "short",
                                day: "numeric",
                                month: "short"
                            }
                        );


                    const dayWeather =
                        weatherDetails(
                            data.daily.weather_code[index]
                        );


                    const icon =
                        document.createElement(
                            "span"
                        );


                    icon.className =
                        "weather-icon";


                    icon.setAttribute(
                        "aria-hidden",
                        "true"
                    );


                    icon.textContent =
                        dayWeather.icon;


                    const condition =
                        document.createElement(
                            "p"
                        );


                    condition.textContent =
                        dayWeather.label;


                    const temperatures =
                        document.createElement(
                            "p"
                        );


                    temperatures.textContent =
                        `${formatNumber(
                            data.daily.temperature_2m_max[index]
                        )}° / ${formatNumber(
                            data.daily.temperature_2m_min[index]
                        )}°`;


                    const rain =
                        document.createElement(
                            "small"
                        );


                    rain.textContent =
                        `বৃষ্টির সম্ভাবনা ${formatNumber(
                            data.daily.precipitation_probability_max[index]
                        )}%`;


                    day.append(
                        dateElement,
                        icon,
                        condition,
                        temperatures,
                        rain
                    );


                    forecast.append(day);

                }
            );


            results.hidden = false;

            setStatus("");

        }


        /*
         * Search Weather By City / Area
         */

        async function loadByPlace(place) {

            if (!place.trim()) {

                setStatus(
                    "শহর বা এলাকার নাম লিখুন।",
                    true
                );

                return;

            }


            searchButton.disabled = true;

            locationButton.disabled = true;

            results.hidden = true;


            setStatus(
                "এলাকার আবহাওয়ার তথ্য খোঁজা হচ্ছে..."
            );


            try {

                const params =
                    new URLSearchParams({

                        name: place,

                        count: "1",

                        language: "bn",

                        format: "json"

                    });


                const response =
                    await fetch(
                        `https://geocoding-api.open-meteo.com/v1/search?${params}`
                    );


                if (!response.ok) {

                    throw new Error(
                        "অবস্থান খোঁজা যায়নি। আবার চেষ্টা করুন।"
                    );

                }


                const data =
                    await response.json();


                const location =
                    data.results?.[0];


                if (!location) {

                    throw new Error(
                        "এই নামে কোনো এলাকা পাওয়া যায়নি। ইংরেজি বানান দিয়ে চেষ্টা করুন।"
                    );

                }


                const placeLabel =
                    [
                        location.name,
                        location.admin1,
                        location.country
                    ]
                    .filter(Boolean)
                    .join(", ");


                await loadForecast(
                    location.latitude,
                    location.longitude,
                    placeLabel
                );


            } catch (error) {

                setStatus(
                    error.message ||
                    "তথ্য আনার সময় সমস্যা হয়েছে।",
                    true
                );

            } finally {

                searchButton.disabled = false;

                locationButton.disabled = false;

            }

        }


        /*
         * Search Form
         */

        form.addEventListener(
            "submit",
            event => {

                event.preventDefault();

                loadByPlace(
                    locationInput.value
                );

            }
        );


        /*
         * Use Current Location
         */

        locationButton.addEventListener(
            "click",
            () => {

                if (!navigator.geolocation) {

                    setStatus(
                        "এই ব্রাউজারে অবস্থান নির্ণয় সুবিধা নেই।",
                        true
                    );

                    return;

                }


                locationButton.disabled = true;

                searchButton.disabled = true;

                results.hidden = true;


                setStatus(
                    "আপনার অবস্থান নির্ণয় করা হচ্ছে..."
                );


                navigator.geolocation.getCurrentPosition(

                    async position => {

                        try {

                            await loadForecast(

                                position.coords.latitude,

                                position.coords.longitude,

                                "আপনার বর্তমান অবস্থান"

                            );


                        } catch (error) {

                            setStatus(
                                error.message ||
                                "আবহাওয়ার তথ্য আনা যায়নি।",
                                true
                            );

                        } finally {

                            locationButton.disabled = false;

                            searchButton.disabled = false;

                        }

                    },


                    error => {

                        console.error(
                            "Geolocation error:",
                            error
                        );


                        setStatus(
                            "অবস্থান পাওয়া যায়নি। ব্রাউজারে Location Permission দিন অথবা এলাকার নাম লিখুন।",
                            true
                        );


                        locationButton.disabled = false;

                        searchButton.disabled = false;

                    }

                );

            }
        );


        /*
         * Load Dhaka Weather Initially
         */

        loadByPlace(
            locationInput.value
        );

    </script>

</body>

</html>