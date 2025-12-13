// ===================== CONFIG =====================
const API_URL_EVENTS = "http://localhost/2A4/projet/peacelink/api_events.php";
const API_URL_PART   = "http://localhost/2A4/projet/peacelink/api_participations.php";

let events = [];
let currentRole = "client";
let currentEvent = null;
let openedEditCard = null;
let currentOrgId = null;


// ===================== FETCH EVENTS =====================
async function fetchEvents() {
    try {
        const res = await fetch(API_URL_EVENTS + "?action=list");
        events = await res.json();
        renderEventsList();
        renderAdminList();
    } catch (err) {
        console.error("Erreur chargement initiatives :", err);
    }
}


// ===================== UTILITAIRES =====================
function formatDate(d) {
    if (!d) return "";
    return new Date(d).toLocaleDateString("fr-FR", {
        year: "numeric", month: "long", day: "numeric"
    });
}

function createBtn(text, cls, action) {
    const b = document.createElement("button");
    b.textContent = text;
    b.className = cls + " btn-small";
    b.onclick = action;
    return b;
}

function showSection(id) {
    const sections = document.querySelectorAll(".initiatives-main .mission-section");
    sections.forEach(s => s.classList.add("hidden"));

    if (!id) {
        // revenir à la première section (liste)
        const first = document.querySelector(".initiatives-main .mission-section");
        if (first) first.classList.remove("hidden");
    } else {
        const target = document.getElementById(id);
        if (target) target.classList.remove("hidden");
    }
}


// ===================== PARTICIPATIONS : COUNT =====================
async function fetchParticipationCount(eventId) {
    try {
        const res = await fetch(API_URL_PART + "?action=countByEvent&event_id=" + eventId);
        const data = await res.json();
        return data.success ? (data.count || 0) : 0;
    } catch (e) {
        console.error("Erreur countByEvent :", e);
        return 0;
    }
}


// ===================== LISTE DES INITIATIVES =====================
function renderEventsList() {
    const container = document.getElementById("events-list");
    if (!container) return;

    container.innerHTML = "";

    let visible = events.filter(e => currentRole !== "client" || e.status === "validé");

    const loc  = document.getElementById("filter-location")?.value.trim().toLowerCase();
    const cat  = document.getElementById("filter-category")?.value;
    const date = document.getElementById("filter-date")?.value;

    if (loc)  visible = visible.filter(e => (e.location || "").toLowerCase().includes(loc));
    if (cat)  visible = visible.filter(e => e.category === cat);
    if (date) visible = visible.filter(e => e.date === date);

    if (!visible.length) {
        container.innerHTML = "<p>Aucune initiative pour le moment.</p>";
        return;
    }

    visible.forEach(evt => {
        const statusTxt =
            evt.status === "validé" ? "Validée" :
            evt.status === "en_attente" ? "En attente" : "Refusée";

        const card = document.createElement("article");
        card.className = "mission-card event-card";

        card.innerHTML = `
            <h3>${evt.title}</h3>
            <p class="event-meta">
                <strong>${evt.category}</strong> • ${evt.location} • ${formatDate(evt.date)}
            </p>
            <p>${evt.description || ""}</p>
            <p class="event-status">
                Statut : <span class="status-badge ${evt.status}">${statusTxt}</span>
            </p>
            <button class="btn-secondary btn-view-event" data-id="${evt.id}">
                Voir les détails
            </button>
        `;

        if (currentRole === "admin" || (currentRole === "organisation" && evt.org_id === currentOrgId)) {
            const actions = document.createElement("div");
            actions.className = "event-admin-inline";

            actions.append(
                createBtn("Modifier", "btn-secondary", () => openInlineEditor(evt.id)),
                createBtn("Supprimer", "btn-danger", () => deleteEvent(evt.id))
            );

            if (currentRole === "admin" && evt.status === "en_attente") {
                actions.append(
                    createBtn("Valider", "btn-success", () => updateEventStatus(evt.id, "validé")),
                    createBtn("Refuser", "btn-danger", () => updateEventStatus(evt.id, "refusé"))
                );
            }

            card.appendChild(actions);
        }

        container.appendChild(card);
    });

    document.querySelectorAll(".btn-view-event").forEach(btn =>
        btn.addEventListener("click", () => openEventDetail(btn.dataset.id))
    );
}


// ===================== INLINE EDITOR =====================
function openInlineEditor(id) {
    const evt = events.find(e => String(e.id) === String(id));
    if (!evt) return;

    if (openedEditCard) openedEditCard.remove();

    const card = [...document.querySelectorAll(".event-card")]
        .find(c => c.querySelector(".btn-view-event")?.dataset.id == id);

    if (!card) return;

    const box = document.createElement("div");
    box.className = "inline-edit-form";

    box.innerHTML = `
        <label>Titre :</label><input id="e-title" value="${evt.title}">
        <label>Catégorie :</label><input id="e-cat" value="${evt.category}">
        <label>Lieu :</label><input id="e-loc" value="${evt.location}">
        <label>Date :</label><input type="date" id="e-date" value="${evt.date}">
        <label>Capacité :</label><input type="number" id="e-cap" value="${evt.capacity}">
        <label>Description :</label><textarea id="e-desc">${evt.description || ""}</textarea>

        <button class="btn-success btn-small" id="saveEdit">Enregistrer</button>
        <button class="btn-danger btn-small" id="cancelEdit">Annuler</button>
    `;

    card.appendChild(box);
    openedEditCard = box;

    document.getElementById("cancelEdit").onclick = () => box.remove();
    document.getElementById("saveEdit").onclick = async () => {
        await updateEvent({
            id: evt.id,
            title: document.getElementById("e-title").value.trim(),
            category: document.getElementById("e-cat").value.trim(),
            location: document.getElementById("e-loc").value.trim(),
            date: document.getElementById("e-date").value,
            capacity: parseInt(document.getElementById("e-cap").value, 10),
            description: document.getElementById("e-desc").value.trim()
        });
        box.remove();
    };
}


// ===================== UPDATE EVENT =====================
async function updateEvent(data) {
    try {
        const res = await fetch(API_URL_EVENTS + "?action=update", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify(data)
        });

        const r = await res.json();
        if (r.success) await fetchEvents();
        else alert("Erreur lors de la mise à jour.");
    } catch (e) {
        console.error("Erreur update :", e);
    }
}


// ===================== UPDATE STATUT =====================
async function updateEventStatus(id, status) {
    try {
        const res = await fetch(API_URL_EVENTS + "?action=updateStatus", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify({ eventId: id, status })
        });

        const r = await res.json();
        if (r.success) await fetchEvents();
        else alert("Erreur changement statut.");
    } catch (e) {
        console.error("Erreur statut :", e);
    }
}


// ===================== DELETE EVENT =====================
async function deleteEvent(id) {
    if (!confirm("Supprimer cette initiative ?")) return;

    try {
        const res = await fetch(API_URL_EVENTS + "?action=delete", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify({ id })
        });

        const r = await res.json();
        if (r.success) await fetchEvents();
        else alert("Erreur suppression.");
    } catch (e) {
        console.error("Erreur suppression :", e);
    }
}


// ===================== DETAIL D'UNE INITIATIVE =====================
async function openEventDetail(id) {
    currentEvent = events.find(e => String(e.id) === String(id));
    if (!currentEvent) return;

    const card     = document.getElementById("event-detail-card");
    const feedback = document.getElementById("participation-feedback");
    const partForm = document.querySelector(".participation-form-wrapper");

    card.innerHTML = `
        <h2>${currentEvent.title}</h2>
        <p class="event-meta">
            <strong>${currentEvent.category}</strong> • 
            ${currentEvent.location} • ${formatDate(currentEvent.date)}
        </p>
        <p>${currentEvent.description || ""}</p>
    `;

    if (partForm) partForm.style.display = "none";
    if (feedback) {
        feedback.textContent = "";
        feedback.style.color = "";
    }

    if (currentRole === "client" && currentEvent.status === "validé") {
        const count = await fetchParticipationCount(currentEvent.id);
        if (count < currentEvent.capacity) {
            if (partForm) partForm.style.display = "block";
        } else if (feedback) {
            feedback.textContent = "Capacité atteinte.";
            feedback.style.color = "red";
        }
    }

    showSection("event-detail-section");
}


// ===================== HANDLER PARTICIPATION =====================
async function handleParticipationSubmit(e) {
    if (e) e.preventDefault();

    const fb   = document.getElementById("participation-feedback");
    const name = document.getElementById("participant-name")?.value.trim();
    const mail = document.getElementById("participant-email")?.value.trim();
    const msg  = document.getElementById("participant-message")?.value.trim();

    if (!currentEvent) {
        if (fb) {
            fb.textContent = "Aucune initiative sélectionnée.";
            fb.style.color = "red";
        }
        return;
    }

    if (!name || !mail) {
        if (fb) {
            fb.textContent = "Nom et email obligatoires.";
            fb.style.color = "red";
        }
        return;
    }

    try {
        const res = await fetch(API_URL_PART + "?action=create", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify({
                event_id: currentEvent.id,
                fullname: name,
                email   : mail,
                message : msg || ""
            })
        });

        const data = await res.json();

        if (data.success) {
            if (fb) {
                fb.textContent = "Participation enregistrée.";
                fb.style.color = "green";
            }
            const form = document.getElementById("participation-form");
            if (form) form.reset();
        } else if (fb) {
            fb.textContent = data.error || "Erreur lors de l'enregistrement.";
            fb.style.color = "red";
        }

    } catch (err) {
        console.error("Erreur participation :", err);
        if (fb) {
            fb.textContent = "Erreur réseau.";
            fb.style.color = "red";
        }
    }
}


// ===================== SETUP PARTICIPATION FORM =====================
function setupParticipationForm() {
    const form  = document.getElementById("participation-form");
    const btn   = form?.querySelector("button[type='submit']");

    if (!form) return;

    form.addEventListener("submit", handleParticipationSubmit);

    if (btn) {
        btn.addEventListener("click", handleParticipationSubmit);
    }
}


// ===================== CRÉATION INITIATIVE =====================
function setupCreateEventForm() {
    const form = document.getElementById("create-event-form");
    if (!form) return;

    form.addEventListener("submit", async e => {
        e.preventDefault();

        const fb   = document.getElementById("create-event-feedback");
        const title = document.getElementById("event-title").value.trim();
        const cat   = document.getElementById("event-category").value;
        const loc   = document.getElementById("event-location").value.trim();
        const date  = document.getElementById("event-date").value;
        const cap   = parseInt(document.getElementById("event-capacity").value, 10);
        const desc  = document.getElementById("event-description").value.trim();
        const org   = document.getElementById("event-org-id").value.trim();

        if (!title || !cat || !loc || !date || !cap || !desc || !org) {
            if (fb) {
                fb.textContent = "Tous les champs sont obligatoires.";
                fb.style.color = "red";
            }
            return;
        }

        if (!currentOrgId) {
            if (fb) {
                fb.textContent = "Veuillez rechoisir le rôle Organisation.";
                fb.style.color = "red";
            }
            return;
        }

        try {
            const res = await fetch(API_URL_EVENTS + "?action=create", {
                method : "POST",
                headers: { "Content-Type": "application/json" },
                body   : JSON.stringify({
                    title,
                    category   : cat,
                    location   : loc,
                    date,
                    capacity   : cap,
                    description: desc,
                    created_by : "Organisation",
                    org_id     : currentOrgId
                })
            });

            const r = await res.json();

            if (r.success) {
                if (fb) {
                    fb.textContent = "Initiative créée.";
                    fb.style.color = "green";
                }
                form.reset();
                await fetchEvents();
                showSection(""); // retour à la liste
            } else if (fb) {
                fb.textContent = r.error || "Erreur.";
                fb.style.color = "red";
            }

        } catch (err) {
            console.error("Erreur création initiative :", err);
            if (fb) {
                fb.textContent = "Erreur réseau.";
                fb.style.color = "red";
            }
        }
    });
}


// ===================== ROLE SWITCHER =====================
function setupRoleSwitcher() {
    const select    = document.getElementById("role-select");
    const createBtn = document.getElementById("btn-open-create");
    const adminSec  = document.getElementById("admin-section");

    if (!select) return;

    function applyRole(role) {
        currentRole = role;
        if (createBtn) createBtn.style.display = (role !== "client") ? "inline-flex" : "none";
        if (adminSec) adminSec.classList.toggle("hidden", role !== "admin");
        renderEventsList();
        renderAdminList();
    }

    select.addEventListener("change", () => {
        const role = select.value;

        if (role === "organisation") {
            let id = prompt("Entrez votre ID Organisation (A-Z, 0-9) :");
            if (!id || !/^[A-Za-z0-9]+$/.test(id.trim())) {
                select.value = "client";
                currentOrgId = null;
                applyRole("client");
                return;
            }
            currentOrgId = id.trim();
        }

        applyRole(role);
    });

    applyRole(select.value);
}


// ===================== FILTRES =====================
function setupFilters() {
    const btn = document.getElementById("btn-apply-filters");
    if (btn) btn.addEventListener("click", renderEventsList);
}


// ===================== NAVIGATION BOUTONS =====================
function setupNavigationButtons() {
    const openCreate  = document.getElementById("btn-open-create");
    const closeCreate = document.getElementById("btn-close-create");
    const backToList  = document.getElementById("btn-back-to-list");

    if (openCreate)  openCreate.onclick  = () => showSection("event-create-section");
    if (closeCreate) closeCreate.onclick = () => showSection("");
    if (backToList)  backToList.onclick  = () => showSection("");
}


// ===================== LISTE ADMIN =====================
function renderAdminList() {
    const container = document.getElementById("admin-events-list");
    if (!container) return;

    container.innerHTML = "";

    const pending = events.filter(e => e.status === "en_attente");

    if (!pending.length) {
        container.innerHTML = "<p>Aucune initiative en attente.</p>";
        return;
    }

    pending.forEach(evt => {
        const card = document.createElement("article");
        card.className = "mission-card event-card";

        card.innerHTML = `
            <h3>${evt.title}</h3>
            <p>${evt.category}</p>
            <p>${evt.location}</p>
            <p>${formatDate(evt.date)}</p>
        `;

        const actions = document.createElement("div");
        actions.className = "event-card-footer";

        actions.append(
            createBtn("Valider", "btn-success", () => updateEventStatus(evt.id, "validé")),
            createBtn("Refuser", "btn-danger", () => updateEventStatus(evt.id, "refusé")),
            createBtn("Modifier", "btn-secondary", () => openInlineEditor(evt.id)),
            createBtn("Supprimer", "btn-danger", () => deleteEvent(evt.id))
        );

        card.appendChild(actions);
        container.appendChild(card);
    });
}


// ===================== INIT =====================
document.addEventListener("DOMContentLoaded", async () => {
    setupRoleSwitcher();
    setupCreateEventForm();
    setupParticipationForm();
    setupNavigationButtons();
    setupFilters();
    await fetchEvents();
});
/* ===================== CHATBOT IA PEACELINK (Google Gemini v1) ===================== */

const GOOGLE_API_KEY = "AIzaSyCetPuNl83sRLpdpRNIsVmfMhyKMkeg0vA";

/* =========== ELEMENTS HTML =========== */

const botBtn = document.getElementById("chatbot-button");
const botBox = document.getElementById("chatbot-box");
const botClose = document.getElementById("chatbot-close");
const botSend = document.getElementById("chatbot-send");
const botInput = document.getElementById("chatbot-input");
const botMessages = document.getElementById("chatbot-messages");

/* =========== OUVRIR / FERMER =========== */

botBtn?.addEventListener("click", () => botBox.classList.toggle("hidden"));
botClose?.addEventListener("click", () => botBox.classList.add("hidden"));

/* =========== AFFICHAGE MESSAGE =========== */

function addMessage(text, sender) {
    const msg = document.createElement("div");
    msg.className = sender === "user" ? "msg user-msg" : "msg bot-msg";
    msg.textContent = text;
    botMessages.appendChild(msg);
    botMessages.scrollTop = botMessages.scrollHeight;
}

/* =========== ENVOI MESSAGE =========== */

async function sendMessage() {
    const userText = botInput.value.trim();
    if (!userText) return;

    addMessage(userText, "user");
    botInput.value = "";

    addMessage("⏳ Réflexion…", "bot");

    try {
        const response = await fetch(
            `https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=${GOOGLE_API_KEY}`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    contents: [
                        {
                            role: "user",
                            parts: [{ text: userText }]
                        }
                    ]
                })
            }
        );

        const data = await response.json();
        console.log("GEMINI RESPONSE:", data);

        // Supprimer "Réflexion…"
        botMessages.removeChild(botMessages.lastChild);

        if (data.error) {
            addMessage("❌ API Error: " + data.error.message, "bot");
            return;
        }

        const botReply =
            data.candidates?.[0]?.content?.parts?.[0]?.text ||
            "❌ Réponse vide";

        addMessage(botReply, "bot");

    } catch (err) {
        console.error(err);
        addMessage("❌ Erreur réseau.", "bot");
    }
}

/* =========== EVENTS =========== */

botSend?.addEventListener("click", sendMessage);

botInput?.addEventListener("keypress", e => {
    if (e.key === "Enter") sendMessage();
});
