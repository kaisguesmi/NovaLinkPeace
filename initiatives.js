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
        // revenir à la première section (liste)
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

    // Filtrer selon le rôle : Client ne voit que "validé", Admin/Org voient tout
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

        // Construction de la carte HTML
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

        // Actions Organisation / Admin
        if (currentRole === "admin" || (currentRole === "organisation" && evt.org_id === currentOrgId)) {
            const actions = document.createElement("div");
            actions.className = "event-admin-inline";

            actions.append(
                createBtn("Modifier", "btn-secondary", () => openInlineEditor(evt.id)),
                createBtn("Supprimer", "btn-danger", () => deleteEvent(evt.id))
            );

            // Validation Admin
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

    // Ajouter les écouteurs sur les boutons "Voir détails"
    document.querySelectorAll(".btn-view-event").forEach(btn =>
        btn.addEventListener("click", () => openEventDetail(btn.dataset.id))
    );
}


// ===================== ÉDITEUR EN LIGNE (INLINE EDIT) =====================
function openInlineEditor(id) {
    const evt = events.find(e => String(e.id) === String(id));
    if (!evt) return;

    if (openedEditCard) openedEditCard.remove();

    // Trouver la carte DOM correspondante
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


// ===================== API : UPDATE EVENT =====================
async function updateEvent(data) {
    try {
        const res = await fetch(API_URL_EVENTS + "?action=update", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify(data)
        });

        const r = await res.json();
        if (r.success) await fetchEvents();
        else alert("Erreur lors de la mise à jour : " + (r.error || "Inconnue"));
    } catch (e) {
        console.error("Erreur update :", e);
    }
}


// ===================== API : UPDATE STATUS =====================
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


// ===================== API : DELETE EVENT =====================
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


// ===================== PAGE DÉTAIL D'UNE INITIATIVE =====================
async function openEventDetail(id) {
    currentEvent = events.find(e => String(e.id) === String(id));
    if (!currentEvent) return;

    const card     = document.getElementById("event-detail-card");
    const feedback = document.getElementById("participation-feedback");
    const partForm = document.querySelector(".participation-form-wrapper");

    // Affichage des infos
    card.innerHTML = `
        <h2>${currentEvent.title}</h2>
        <p class="event-meta">
            <strong>${currentEvent.category}</strong> • 
            ${currentEvent.location} • ${formatDate(currentEvent.date)}
        </p>
        <p class="event-description">${currentEvent.description || "Pas de description."}</p>
        <p><strong>Organisation :</strong> ${currentEvent.created_by || "Anonyme"}</p>
    `;

    // Reset formulaire
    if (partForm) partForm.style.display = "none";
    if (feedback) {
        feedback.textContent = "";
        feedback.style.color = "";
    }

    // Gestion de l'affichage du formulaire de participation
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

    if (!currentEvent) {
        if (fb) { fb.textContent = "Erreur : Aucune initiative sélectionnée."; fb.style.color = "red"; }
        return;
    }

    if (!name || !mail) {
        if (fb) { fb.textContent = "Nom et email obligatoires."; fb.style.color = "red"; }
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
            if (fb) { fb.textContent = "Participation enregistrée avec succès !"; fb.style.color = "green"; }
            const form = document.getElementById("participation-form");
            if (form) form.reset();
            openEventDetail(currentEvent.id); // Recharger pour maj compteur
        } else {
            if (fb) { fb.textContent = data.error || "Erreur lors de l'enregistrement."; fb.style.color = "red"; }
        }

    } catch (err) {
        console.error("Erreur participation :", err);
        if (fb) { fb.textContent = "Erreur réseau."; fb.style.color = "red"; }
    }
}

function setupParticipationForm() {
    const form  = document.getElementById("participation-form");
    if (!form) return;
    form.addEventListener("submit", handleParticipationSubmit);
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
            if (fb) { fb.textContent = "Tous les champs sont obligatoires."; fb.style.color = "red"; }
            return;
        }

        if (!currentOrgId && currentRole === "organisation") {
             currentOrgId = org; 
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
                    created_by : "Organisation " + org,
                    org_id     : org
                })
            });

            const r = await res.json();

            if (r.success) {
                if (fb) { fb.textContent = "Initiative créée avec succès (en attente de validation)."; fb.style.color = "green"; }
                form.reset();
                await fetchEvents();
                setTimeout(() => showSection(""), 1500); 
            } else {
                if (fb) { fb.textContent = r.error || "Erreur."; fb.style.color = "red"; }
            }

        } catch (err) {
            console.error("Erreur création initiative :", err);
            if (fb) { fb.textContent = "Erreur réseau."; fb.style.color = "red"; }
        }
    });
}


// ===================== RÔLES & INTERFACE =====================
function setupRoleSwitcher() {
    const select    = document.getElementById("role-select");
    const createBtn = document.getElementById("btn-open-create");
    const adminSec  = document.getElementById("admin-section");

    if (!select) return;

    function applyRole(role) {
        currentRole = role;
        // Bouton créer visible pour Organisation et Admin
        if (createBtn) createBtn.style.display = (role === "organisation" || role === "admin") ? "inline-flex" : "none";
        
        // Section validation visible seulement pour admin
        if (adminSec) adminSec.classList.toggle("hidden", role !== "admin");
        
        renderEventsList();
        renderAdminList();
    }

    select.addEventListener("change", () => {
        const role = select.value;

        if (role === "organisation") {
            let id = prompt("Veuillez entrer votre ID Organisation (simulation) :");
            if (!id || id.trim() === "") {
                alert("ID requis. Retour au rôle Client.");
                select.value = "client";
                applyRole("client");
                return;
            }
            currentOrgId = id.trim();
        } else {
            currentOrgId = null;
        }

        applyRole(role);
    });

    applyRole(select.value);
}


// ===================== FILTRES & NAVIGATION =====================
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


// ===================== SECTION ADMIN (VALIDATION) =====================
function renderAdminList() {
    const container = document.getElementById("admin-events-list");
    if (!container) return;

    container.innerHTML = "";

    const pending = events.filter(e => e.status === "en_attente");

    if (!pending.length) {
        container.innerHTML = "<p>Aucune initiative en attente de validation.</p>";
        return;
    }

    pending.forEach(evt => {
        const card = document.createElement("article");
        card.className = "mission-card event-card";
        card.style.borderColor = "orange"; // Visuel pour dire "attention"

        card.innerHTML = `
            <h3>${evt.title}</h3>
            <p>${evt.category} - ${evt.location}</p>
            <p>Organisation: ${evt.created_by}</p>
            <p><em>${evt.description}</em></p>
        `;

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
   ===================== CHATBOT SIMULÉ (SANS CLÉ API) ====================
   ======================================================================== 
   Ce chatbot répond automatiquement en analysant les mots-clés.
   Il n'utilise PAS d'API externe (Google/OpenAI) pour éviter les erreurs.
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
    msg.innerHTML = text; // Autorise le HTML (gras, br)
    botMessages.appendChild(msg);
    botMessages.scrollTop = botMessages.scrollHeight;
}

// --- LOGIQUE INTELLIGENTE SIMULÉE ---
function getSimulatedResponse(input) {
    const text = input.toLowerCase();
    
    // 1. Salutations
    if (text.includes("bonjour") || text.includes("salut") || text.includes("hello") || text.includes("ça va")) {
        return "Bonjour ! 👋 Je suis l'assistant PeaceLink. Comment puis-je vous aider aujourd'hui ? (Initiatives, Connexion...)";
    }

    // 2. Problèmes de Connexion (Login, Mot de passe)
    if (text.includes("connexion") || text.includes("connect") || text.includes("login") || text.includes("mot de passe") || text.includes("compte")) {
        return `
        <strong>Problème de connexion ?</strong> 🔐<br>
        Voici quelques étapes pour vous aider :<br>
        1. Vérifiez que votre email et mot de passe sont corrects.<br>
        2. Si vous avez oublié votre mot de passe, utilisez le lien "Mot de passe oublié" sur la page de login.<br>
        3. Si le problème persiste, contactez l'admin via le formulaire de contact.
        `;
    }

    // 3. Demande d'initiatives ou Recherche
    if (text.includes("initiative") || text.includes("événement") || text.includes("event") || text.includes("activit") || text.includes("cherch")) {
        // On récupère les vrais événements chargés
        const validEvents = events.filter(e => e.status === 'validé');
        
        if (validEvents.length === 0) return "Désolé, il n'y a aucune initiative validée pour le moment.";

        let response = "Voici les initiatives disponibles actuellement :<br><br>";
        validEvents.slice(0, 3).forEach(e => {
            response += `🌱 <strong>${e.title}</strong> (${e.category})<br>📍 ${e.location} - 📅 ${formatDate(e.date)}<br><br>`;
        });
        return response + "Cliquez sur 'Voir les détails' pour participer !";
    }

    // 4. Conseil / Recommandation / Ennui
    if (text.includes("conseil") || text.includes("suggèr") || text.includes("ennui") || text.includes("quoi faire")) {
        const validEvents = events.filter(e => e.status === 'validé');
        if (validEvents.length > 0) {
            // Prend un événement au hasard
            const randomEvent = validEvents[Math.floor(Math.random() * validEvents.length)];
            return `
            Tu ne sais pas quoi faire ? 🤔<br>
            Je te conseille vivement cette initiative :<br>
            ✨ <strong>${randomEvent.title}</strong> !<br>
            C'est une action de type <em>${randomEvent.category}</em> qui se déroule à ${randomEvent.location}.<br>
            Ça te tente ?
            `;
        } else {
            return "Je n'ai pas d'initiative à te conseiller pour le moment, reviens plus tard !";
        }
    }

    // 5. Par défaut
    return "Je ne suis pas sûr de comprendre. Pouvez-vous reformuler ? Je peux parler des <strong>initiatives</strong> ou vous aider pour la <strong>connexion</strong>.";
}

// --- Envoi du message ---
function sendMessage() {
    const userText = botInput.value.trim();
    if (!userText) return;

    addMessage(userText, "user");
    botInput.value = "";
    
    // Simulation d'un délai de réflexion ("typing...")
    const loadingId = "loading-" + Date.now();
    const loadingMsg = document.createElement("div");
    loadingMsg.className = "msg bot-msg";
    loadingMsg.id = loadingId;
    loadingMsg.innerText = "PeaceLink réfléchit...";
    botMessages.appendChild(loadingMsg);
    botMessages.scrollTop = botMessages.scrollHeight;

    setTimeout(() => {
        // Supprimer le message de chargement
        const loader = document.getElementById(loadingId);
        if (loader) loader.remove();

        // Obtenir la réponse simulée
        const response = getSimulatedResponse(userText);
        addMessage(response, "bot");

    }, 800); // Délai de 800ms pour faire réaliste
}

// --- Listeners Chatbot ---
if (botSend) botSend.addEventListener("click", sendMessage);
if (botInput) {
    botInput.addEventListener("keypress", e => {
        if (e.key === "Enter") sendMessage();
    });
}


// ===================== INITIALISATION GLOBALE =====================
document.addEventListener("DOMContentLoaded", async () => {
    // 1. Configuration des boutons et formulaires
    setupRoleSwitcher();
    setupCreateEventForm();
    setupParticipationForm();
    setupNavigationButtons();
    setupFilters();
    
    // 2. Chargement des données initiales
    await fetchEvents();
    
    // 3. Message d'accueil du bot (optionnel)
    setTimeout(() => {
        if (botMessages && botMessages.children.length === 0) {
            addMessage("Bonjour ! Je suis l'assistant PeaceLink. Je peux vous aider à trouver une initiative ou résoudre un problème de connexion. 😊", "bot");
        }
    }, 1000);
});