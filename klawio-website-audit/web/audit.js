document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("klw-audit-form");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const url = document.getElementById("klw-url").value.trim();
        const email = document.getElementById("klw-email").value.trim();
        const status = document.getElementById("klw-status");
        const results = document.getElementById("klw-results");

        status.innerHTML = "Analyzing...";
        results.classList.add("hidden");

        const res = await fetch("../api/api.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ url, email })
        });

        const data = await res.json();

        if (data.error) {
            status.innerHTML = data.error;
            return;
        }

        document.getElementById("klw-score-overall").innerText = data.overall_score + "/100";
        document.getElementById("klw-score-performance").innerText = data.performance_score + "/100";
        document.getElementById("klw-score-seo").innerText = data.seo_score + "/100";
        document.getElementById("klw-score-mobile").innerText = data.mobile_score + "/100";

        const ul = document.getElementById("klw-key-findings");
        ul.innerHTML = "";
        data.findings.forEach(f => {
            const li = document.createElement("li");
            li.innerText = f;
            ul.appendChild(li);
        });

        status.innerHTML = "Audit completed!";
        results.classList.remove("hidden");
    });
});
