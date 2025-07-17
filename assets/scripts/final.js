function submitTest3() {
    const form = document.getElementById("testForm");
    const resultsDiv = document.getElementById("results");

    const answers = {
        q1: "c", q2: "a", q3: "a", q4: "a", q5: "a",
        q6: "c", q7: "c", q8: "a", q9: "b", q10: "a",
        q11: "b", q12: "b", q13: "a", q14: "a", q15: "c",
        q16: "b", q17: "a", q18: "a", q19: "c", q20: "b",
        q21: "b", q22: "b", q23: "c", q24: "b", q25: "b",
        q26: "a", q27: "b", q28: "a", q29: "c", q30: "c",
        q31: "a", q32: "a", q33: "a", q34: "b", q35: "a",
        q36: "b", q37: "a", q38: "a", q39: "c", q40: "a",
        q41: "c", q42: "b", q43: "a", q44: "a", q45: "a",
        q46: "b", q47: "a", q48: "a", q49: "a", q50: "b",
        q51: "a", q52: "b", q53: "b", q54: "a", q55: "c",
        q56: "b", q57: "b", q58: "a", q59: "b", q60: "a",
        q61: "b", q62: "c", q63: "b", q64: "c", q65: "b",
        q66: "a", q67: "a", q68: "c", q69: "a", q70: "b",
        q71: "c", q72: "b", q73: "c", q74: "c", q75: "a",
        q76: "b", q77: "b", q78: "b", q79: "c", q80: "b",
        q81: "b", q82: "c", q83: "c", q84: "b", q85: "a",
        q86: "b", q87: "b", q88: "b", q89: "c", q90: "a",
        q91: "a", q92: "a", q93: "c", q94: "b", q95: "a",
        q96: "a", q97: "b", q98: "a", q99: "a", q100: "c",
        q101: "c", q102: "c", q103: ["b", "c"], q104: "a", q105: "a",
        q106: "c", q107: "a", q108: "b", q109: "c", q110: "b",
        q111: "c", q112: "a", q113: "a", q114: "a", q115: "a",
        q116: "a", q117: "b", q118: "b", q119: "b", q120: "a"
    };

    let score = 0;

    for (const question in answers) {
        const selectedAnswer = form[question]?.value;
        if (selectedAnswer === answers[question]) {
            score++;
        }
    }

    // ✅ Show score to user
    resultsDiv.innerHTML = `Your score: ${score} out of ${Object.keys(answers).length}`;

    // ✅ Save score to localStorage
    localStorage.setItem("finalScore", score);

    // ✅ Send score to backend using your existing save_progress.php
    fetch("../api/save_progress.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `lesson_id=final_test&completion_percentage=100&score=${score}`
    })
    .then(res => res.text())
    .then(response => {
        console.log("Score successfully saved:", response);
    })
    .catch(err => {
        console.error("Error saving score:", err);
        alert("There was a problem saving your score.");
    });
}
