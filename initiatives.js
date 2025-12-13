// ===================== CONFIGURATION GLOBALE =====================
const API_URL_EVENTS = "http://localhost/2A4/projet/peacelink/api_events.php";
const API_URL_PART   = "http://localhost/2A4/projet/peacelink/api_participations.php";

// ===================== VARIABLES D'ÉTAT =====================
let events = [];
let currentRole = "client"; // client | organisation | admin
let currentEvent = null;
let openedEditCard = null;
let currentOrgId = null;


// ===================== CHARGEMENT DES DONNÉES (FETCH) =====================
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
        const first = document.querySelector(".initiatives-main .mission-section");
        if (first) first.classList.remove("hidden");
    } else {
        const target = document.getElementById(id);
        if (target) target.classList.remove("hidden");
    }
}


// ===================== PARTICIPATIONS : COMPTEUR =====================
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


// ===================== AFFICHAGE LISTE INITIATIVES =====================
function renderEventsList() {
    const container = document.getElementById("events-list");
    if (!container) return;

    container.innerHTML = "";

    // Filtrer selon le rôle
    let visible = events.filter(e => currentRole !== "client" || e.status === "validé");

    // Récupération des filtres
    const loc  = document.getElementById("filter-location")?.value.trim().toLowerCase();
    const cat  = document.getElementById("filter-category")?.value;
    const date = document.getElementById("filter-date")?.value;

    // Application des filtres
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


// ===================== ÉDITEUR EN LIGNE (INLINE EDIT) =====================
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
        <div style="margin-top:10px; padding:10px; background:#f9f9f9; border:1px solid #ddd;">
            <h4>Modification Rapide</h4>
            <label>Titre :</label><input id="e-title" value="${evt.title}" style="width:100%; margin-bottom:5px;">
            <label>Catégorie :</label><input id="e-cat" value="${evt.category}" style="width:100%; margin-bottom:5px;">
            <label>Lieu :</label><input id="e-loc" value="${evt.location}" style="width:100%; margin-bottom:5px;">
            <label>Date :</label><input type="date" id="e-date" value="${evt.date}" style="width:100%; margin-bottom:5px;">
            <label>Capacité :</label><input type="number" id="e-cap" value="${evt.capacity}" style="width:100%; margin-bottom:5px;">
            <label>Description :</label><textarea id="e-desc" style="width:100%; margin-bottom:5px;">${evt.description || ""}</textarea>

            <button class="btn-success btn-small" id="saveEdit">Enregistrer</button>
            <button class="btn-danger btn-small" id="cancelEdit">Annuler</button>
        </div>
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


// ===================== API : UPDATE, STATUS, DELETE =====================
async function updateEvent(data) {
    try {
        const res = await fetch(API_URL_EVENTS + "?action=update", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify(data)
        });
        const r = await res.json();
        if (r.success) await fetchEvents();
        else alert("Erreur maj : " + (r.error || "Inconnue"));
    } catch (e) { console.error("Erreur update :", e); }
}

async function updateEventStatus(id, status) {
    try {
        const res = await fetch(API_URL_EVENTS + "?action=updateStatus", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify({ eventId: id, status })
        });
        const r = await res.json();
        if (r.success) await fetchEvents();
    } catch (e) { console.error("Erreur statut :", e); }
}

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
    } catch (e) { console.error("Erreur suppression :", e); }
}


// ===================== PAGE DÉTAIL D'UNE INITIATIVE =====================
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
        <p class="event-description">${currentEvent.description || "Pas de description."}</p>
        <p><strong>Organisation :</strong> ${currentEvent.created_by || "Anonyme"}</p>
    `;

    if (partForm) partForm.style.display = "none";
    if (feedback) { feedback.textContent = ""; feedback.style.color = ""; }

    if (currentRole === "client" && currentEvent.status === "validé") {
        const count = await fetchParticipationCount(currentEvent.id);
        const placesRestantes = currentEvent.capacity - count;
        
        card.innerHTML += `<p><em>Places restantes : ${placesRestantes} / ${currentEvent.capacity}</em></p>`;

        if (count < currentEvent.capacity) {
            if (partForm) partForm.style.display = "block";
        } else if (feedback) {
            feedback.textContent = "Désolé, cet événement est complet.";
            feedback.style.color = "red";
            if (partForm) partForm.style.display = "block";
        }
    }
    showSection("event-detail-section");
}


// ===================== GESTION PARTICIPATION (SUBMIT) =====================
async function handleParticipationSubmit(e) {
    if (e) e.preventDefault();

    const fb   = document.getElementById("participation-feedback");
    const name = document.getElementById("participant-name")?.value.trim();
    const mail = document.getElementById("participant-email")?.value.trim();
    const msg  = document.getElementById("participant-message")?.value.trim();

    if (!currentEvent) { if (fb) { fb.textContent = "Erreur initiative."; fb.style.color = "red"; } return; }
    if (!name || !mail) { if (fb) { fb.textContent = "Nom et email obligatoires."; fb.style.color = "red"; } return; }

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
            if (fb) { fb.textContent = "Participation enregistrée !"; fb.style.color = "green"; }
            const form = document.getElementById("participation-form");
            if (form) form.reset();
            openEventDetail(currentEvent.id);
        } else {
            if (fb) { fb.textContent = data.error || "Erreur."; fb.style.color = "red"; }
        }
    } catch (err) {
        console.error("Erreur participation :", err);
        if (fb) { fb.textContent = "Erreur réseau."; fb.style.color = "red"; }
    }
}
function setupParticipationForm() {
    const form  = document.getElementById("participation-form");
    if (form) form.addEventListener("submit", handleParticipationSubmit);
}


// ===================== CRÉATION INITIATIVE (SUBMIT) =====================
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
            if (fb) { fb.textContent = "Tous les champs sont obligatoires."; fb.style.color = "red"; } return;
        }
        if (!currentOrgId && currentRole === "organisation") currentOrgId = org; 

        try {
            const res = await fetch(API_URL_EVENTS + "?action=create", {
                method : "POST",
                headers: { "Content-Type": "application/json" },
                body   : JSON.stringify({
                    title, category: cat, location: loc, date, capacity: cap, description: desc,
                    created_by: "Organisation " + org, org_id: org
                })
            });
            const r = await res.json();

            if (r.success) {
                if (fb) { fb.textContent = "Initiative créée (en attente)."; fb.style.color = "green"; }
                form.reset();
                await fetchEvents();
                setTimeout(() => showSection(""), 1500); 
            } else {
                if (fb) { fb.textContent = r.error || "Erreur."; fb.style.color = "red"; }
            }
        } catch (err) {
            console.error("Erreur création :", err);
            if (fb) { fb.textContent = "Erreur réseau."; fb.style.color = "red"; }
        }
    });
}


// ===================== RÔLES & FILTRES & ADMIN =====================
function setupRoleSwitcher() {
    const select    = document.getElementById("role-select");
    const createBtn = document.getElementById("btn-open-create");
    const adminSec  = document.getElementById("admin-section");

    if (!select) return;

    function applyRole(role) {
        currentRole = role;
        if (createBtn) createBtn.style.display = (role === "organisation" || role === "admin") ? "inline-flex" : "none";
        if (adminSec) adminSec.classList.toggle("hidden", role !== "admin");
        renderEventsList();
        renderAdminList();
    }

    select.addEventListener("change", () => {
        const role = select.value;
        if (role === "organisation") {
            let id = prompt("ID Organisation (simulation) :");
            if (!id || id.trim() === "") {
                alert("ID requis. Retour au rôle Client.");
                select.value = "client";
                applyRole("client");
                return;
            }
            currentOrgId = id.trim();
        } else { currentOrgId = null; }
        applyRole(role);
    });
    applyRole(select.value);
}

function setupFilters() {
    const btn = document.getElementById("btn-apply-filters");
    if (btn) btn.addEventListener("click", renderEventsList);
}

function setupNavigationButtons() {
    const openCreate  = document.getElementById("btn-open-create");
    const closeCreate = document.getElementById("btn-close-create");
    const backToList  = document.getElementById("btn-back-to-list");
    if (openCreate)  openCreate.onclick  = () => showSection("event-create-section");
    if (closeCreate) closeCreate.onclick = () => showSection("");
    if (backToList)  backToList.onclick  = () => showSection("");
}

function renderAdminList() {
    const container = document.getElementById("admin-events-list");
    if (!container) return;
    container.innerHTML = "";
    const pending = events.filter(e => e.status === "en_attente");
    if (!pending.length) { container.innerHTML = "<p>Aucune initiative en attente.</p>"; return; }

    pending.forEach(evt => {
        const card = document.createElement("article");
        card.className = "mission-card event-card";
        card.style.borderColor = "orange"; 
        card.innerHTML = `<h3>${evt.title}</h3><p>${evt.category} - ${evt.location}</p><p>Org: ${evt.created_by}</p>`;
        const actions = document.createElement("div");
        actions.className = "event-card-footer";
        actions.append(
            createBtn("Valider", "btn-success", () => updateEventStatus(evt.id, "validé")),
            createBtn("Refuser", "btn-danger", () => updateEventStatus(evt.id, "refusé")),
            createBtn("Supprimer", "btn-danger", () => deleteEvent(evt.id))
        );
        card.appendChild(actions);
        container.appendChild(card);
    });
}


/* ========================================================================
   ===================== CHATBOT INTELLIGENT (SIMULÉ) =====================
   ======================================================================== */

// --- Éléments du DOM ---
const botBtn = document.getElementById("chatbot-button");
const botBox = document.getElementById("chatbot-box");
const botClose = document.getElementById("chatbot-close");
const botSend = document.getElementById("chatbot-send");
const botInput = document.getElementById("chatbot-input");
const botMessages = document.getElementById("chatbot-messages");

// --- Ouverture / Fermeture ---
if (botBtn) botBtn.addEventListener("click", () => botBox.classList.toggle("hidden"));
if (botClose) botClose.addEventListener("click", () => botBox.classList.add("hidden"));

// --- Affichage des messages ---
function addMessage(text, sender) {
    const msg = document.createElement("div");
    msg.className = sender === "user" ? "msg user-msg" : "msg bot-msg";
    msg.innerHTML = text; // Autorise le HTML
    botMessages.appendChild(msg);
    botMessages.scrollTop = botMessages.scrollHeight;
}

// --- LOGIQUE CERVEAU DU BOT ---
function getSimulatedResponse(input) {
    const text = input.toLowerCase();
    
    // 1. Salutations
    if (text.includes("bonjour") || text.includes("salut") || text.includes("hello")) {
        return "Bonjour ! 👋 Je suis l'assistant PeaceLink. Cliquez sur un bouton ci-dessus ou posez-moi une question.";
    }

    // 2. Problèmes de Connexion
    if (text.includes("connexion") || text.includes("connect") || text.includes("mot de passe")) {
        return `
        <strong>Problème de connexion ?</strong> 🔐<br>
        1. Vérifiez vos identifiants.<br>
        2. Utilisez "Mot de passe oublié".<br>
        3. Contactez l'admin si ça persiste.
        `;
    }

    // 3. Citations (Easter Egg)
    if (text.includes("citation") || text.includes("inspire") || text.includes("paix")) {
        const quotes = [
            "La paix commence par un sourire. – Mère Teresa",
            "Soyez le changement que vous voulez voir dans le monde. – Gandhi",
            "Il n'y a pas de chemin vers la paix, la paix est le chemin. – Gandhi"
        ];
        const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
        return `🕊️ <em>"${randomQuote}"</em>`;
    }

    // 4. Recherche par VILLE (Géolocalisation simulée)
    const validEvents = events.filter(e => e.status === 'validé');
    const cities = [...new Set(validEvents.map(e => e.location.toLowerCase()))];
    const foundCity = cities.find(city => text.includes(city));

    if (foundCity) {
        const cityEvents = validEvents.filter(e => e.location.toLowerCase().includes(foundCity));
        let response = `🔎 Initiatives à <strong>${foundCity.toUpperCase()}</strong> :<br>`;
        cityEvents.forEach(e => {
            response += `
            <div style="background:white; border:1px solid #eee; padding:5px; margin:5px 0; border-radius:5px; font-size:13px;">
                🌱 <strong>${e.title}</strong><br>
                📅 ${formatDate(e.date)}<br>
                <button class="chat-action-btn" onclick="openEventDetail(${e.id})">Voir</button>
            </div>`;
        });
        return response;
    }

    // 5. Recherche par CATÉGORIE
    if (text.includes("écologie") || text.includes("nature") || text.includes("vert")) {
        const ecoEvents = validEvents.filter(e => e.category === 'Écologie');
        if (!ecoEvents.length) return "Pas d'initiative écologique pour l'instant.";
        let res = "🌿 <strong>Initiatives Écologie :</strong><br>";
        ecoEvents.forEach(e => {
            res += `<div style="margin-top:5px;">- ${e.title} <button class="chat-action-btn" onclick="openEventDetail(${e.id})">Voir</button></div>`;
        });
        return res;
    }
    if (text.includes("solidarité") || text.includes("social")) {
        const solEvents = validEvents.filter(e => e.category === 'Solidarité');
        if (!solEvents.length) return "Pas d'initiative solidaire pour l'instant.";
        let res = "🤝 <strong>Initiatives Solidarité :</strong><br>";
        solEvents.forEach(e => {
            res += `<div style="margin-top:5px;">- ${e.title} <button class="chat-action-btn" onclick="openEventDetail(${e.id})">Voir</button></div>`;
        });
        return res;
    }

    // 6. Demande générale Initiatives
    if (text.includes("initiative") || text.includes("événement") || text.includes("voir")) {
        if (validEvents.length === 0) return "Aucune initiative validée pour le moment.";
        let response = "Voici quelques initiatives :<br>";
        validEvents.slice(0, 3).forEach(e => {
            response += `
            <div style="background:white; border:1px solid #eee; padding:5px; margin:5px 0; border-radius:5px; font-size:13px;">
                🌱 <strong>${e.title}</strong> (${e.category})<br>
                📍 ${e.location}<br>
                <button class="chat-action-btn" onclick="openEventDetail(${e.id})">Voir & Participer</button>
            </div>`;
        });
        return response;
    }

    // 7. Conseil / Hasard
    if (text.includes("conseil") || text.includes("ennui") || text.includes("quoi faire")) {
        if (validEvents.length > 0) {
            const r = validEvents[Math.floor(Math.random() * validEvents.length)];
            return `
            💡 <strong>Mon conseil du jour :</strong><br>
            Découvrez l'initiative <em>"${r.title}"</em> !<br>
            C'est à ${r.location} le ${formatDate(r.date)}.<br>
            <button class="chat-action-btn" onclick="openEventDetail(${r.id})">Regarder ça</button>
            `;
        }
        return "Pas d'initiative à conseiller pour l'instant.";
    }

    return "Je n'ai pas compris. Essayez 'Initiatives', 'Connexion', 'Écologie' ou 'Tunis' (par exemple).";
}

// --- Envoi du message ---
function sendMessage() {
    const userText = botInput.value.trim();
    if (!userText) return;

    addMessage(userText, "user");
    botInput.value = "";
    
    // Simulation attente
    const loadingId = "load-" + Date.now();
    const loadingMsg = document.createElement("div");
    loadingMsg.className = "msg bot-msg";
    loadingMsg.id = loadingId;
    loadingMsg.innerText = "Writing...";
    botMessages.appendChild(loadingMsg);
    botMessages.scrollTop = botMessages.scrollHeight;

    setTimeout(() => {
        const loader = document.getElementById(loadingId);
        if (loader) loader.remove();
        const response = getSimulatedResponse(userText);
        addMessage(response, "bot");
    }, 600);
}

// --- Fonctions Globales pour le Chat (Quick Replies) ---
window.sendQuickReply = function(text) {
    const input = document.getElementById("chatbot-input");
    if(input) input.value = text;
    sendMessage();
};

// --- Listeners ---
if (botSend) botSend.addEventListener("click", sendMessage);
if (botInput) {
    botInput.addEventListener("keypress", e => {
        if (e.key === "Enter") sendMessage();
    });
}

// --- Injection des styles CSS du chat (pour éviter de toucher au CSS) ---
function injectChatStyles() {
    const style = document.createElement('style');
    style.innerHTML = `
        .chat-btn {
            background-color: #5DADE2; color: white; border: none; padding: 5px 10px;
            margin: 3px; border-radius: 15px; cursor: pointer; font-size: 12px; transition: 0.2s;
        }
        .chat-btn:hover { background-color: #3498DB; }
        .chat-action-btn {
            background-color: #7BD389; color: white; border: none; padding: 3px 8px;
            border-radius: 4px; cursor: pointer; font-size: 11px; margin-top:3px;
        }
        .chat-action-btn:hover { background-color: #68c37a; }
    `;
    document.head.appendChild(style);
}

// ===================== INITIALISATION GLOBALE =====================
document.addEventListener("DOMContentLoaded", async () => {
    setupRoleSwitcher();
    setupCreateEventForm();
    setupParticipationForm();
    setupNavigationButtons();
    setupFilters();
    injectChatStyles(); // Ajout des styles boutons chat
    
    await fetchEvents();
    
    // Message d'accueil avec Quick Replies
    setTimeout(() => {
        if (botMessages && botMessages.children.length === 0) {
            const welcomeHTML = `
                Bonjour ! Je suis l'assistant PeaceLink. 😊<br>
                Je peux vous aider sur ces sujets :<br><br>
                <button class="chat-btn" onclick="sendQuickReply('Voir les initiatives')">🌱 Initiatives</button>
                <button class="chat-btn" onclick="sendQuickReply('Problème de connexion')">🔐 Connexion</button>
                <button class="chat-btn" onclick="sendQuickReply('Donne-moi un conseil')">💡 Conseil</button>
            `;
            addMessage(welcomeHTML, "bot");
        }
    }, 1000);
});