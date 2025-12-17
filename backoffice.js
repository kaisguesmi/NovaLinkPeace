/**************************************************
 * CONFIG
 **************************************************/
const API_EVENTS = "http://localhost/2A4/projet/peacelink/api_events.php";
const API_PARTICIPATIONS = "http://localhost/2A4/projet/peacelink/api_participations.php";
const API_STORIES = "http://localhost/2A4/projet/peacelink/api_histoires.php";

let events = [];
let participations = [];
let stories = [];
let openedEditId = null;
let editingParticipationId = null;


/**************************************************
 * VALIDATION
 **************************************************/
function validateText(value, label) {
    if (!value || value.trim().length < 2) return `${label} doit contenir au moins 2 caractères.\n`;
    return "";
}

function validateEmail(value) {
    if (!value || !value.includes("@") || !value.includes(".")) return "Email invalide.\n";
    return "";
}

function validateDate(dateStr) {
    if (!dateStr) return "La date est obligatoire.\n";
    const today = new Date(); today.setHours(0,0,0,0);
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return "La date est invalide.\n";
    if (d < today) return "La date doit être aujourd'hui ou dans le futur.\n";
    return "";
}


/**************************************************
 * FETCH DATA
 **************************************************/
async function fetchEvents() {
    try {
        const res = await fetch(API_EVENTS + "?action=list");
        events = await res.json();
        renderInitiativesTable();
        updateDashboardStats();
        fillParticipationEventSelect();
    } catch (err) { console.error("Erreur chargement initiatives :", err); }
}

async function fetchParticipations() {
    try {
        const res = await fetch(API_PARTICIPATIONS + "?action=list");
        participations = await res.json();
        renderParticipationList();
    } catch (err) { console.error("Erreur chargement participations :", err); }
}


/**************************************************
 * INITIATIVES : STATUT / DELETE / EDIT
 **************************************************/
async function setEventStatus(eventId, status) {
    try {
        const res = await fetch(API_EVENTS + "?action=updateStatus", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ eventId, status })
        });
        const data = await res.json();
        if (!data.success) return alert("Erreur lors du changement de statut.");
        await fetchEvents();
    } catch (err) { console.error("Erreur update statut :", err); }
}

async function deleteEvent(eventId) {
    if (!confirm("Voulez-vous vraiment supprimer cette initiative ?")) return;
    try {
        const res = await fetch(API_EVENTS + "?action=delete", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: eventId })
        });
        const data = await res.json();
        if (!data.success) return alert("Erreur lors de la suppression.");
        await fetchEvents();
    } catch (err) { console.error("Erreur DELETE :", err); }
}

function openInlineEditor(eventId) {
    if (openedEditId !== null) {
        const old = document.getElementById("edit-row-" + openedEditId);
        if (old) old.remove();
        openedEditId = null;
    }

    const evt = events.find(e => String(e.id) === String(eventId));
    if (!evt) return;

    openedEditId = eventId;
    const row = document.getElementById("row-" + eventId);
    if (!row) return;

    const formRow = document.createElement("tr");
    formRow.id = "edit-row-" + eventId;

    formRow.innerHTML = `
        <td colspan="7">
            <div class="inline-edit-box">

                <label>Titre :</label>
                <input id="edit-title-${eventId}" value="${evt.title}">

                <label>Catégorie :</label>
                <input id="edit-category-${eventId}" value="${evt.category}">

                <label>Lieu :</label>
                <input id="edit-location-${eventId}" value="${evt.location}">

                <label>Date :</label>
                <input id="edit-date-${eventId}" type="date" value="${evt.date}">

                <label>Capacité :</label>
                <input id="edit-capacity-${eventId}" value="${evt.capacity}">

                <label>Description :</label>
                <textarea id="edit-description-${eventId}">${evt.description || ""}</textarea>

                <div id="edit-error-${eventId}" class="error-message"></div>

                <div class="edit-buttons">
                    <button class="btn-primary" onclick="saveInlineEdit(${eventId})">Enregistrer</button>
                    <button class="btn-secondary" onclick="cancelInlineEdit(${eventId})">Annuler</button>
                </div>

            </div>
        </td>`;
    row.after(formRow);
}

function cancelInlineEdit(id) {
    const f = document.getElementById("edit-row-" + id);
    if (f) f.remove();
    openedEditId = null;
}

async function saveInlineEdit(id) {
    const title = document.getElementById("edit-title-" + id).value;
    const category = document.getElementById("edit-category-" + id).value;
    const location = document.getElementById("edit-location-" + id).value;
    const date = document.getElementById("edit-date-" + id).value;
    const capacityStr = document.getElementById("edit-capacity-" + id).value;
    const description = document.getElementById("edit-description-" + id).value;

    let error = "";
    error += validateText(title, "Titre");
    error += validateText(category, "Catégorie");
    error += validateText(location, "Lieu");
    error += validateDate(date);
    if (!capacityStr || isNaN(capacityStr) || parseInt(capacityStr) <= 0)
        error += "Capacité doit être un nombre positif.\n";

    const errorDiv = document.getElementById("edit-error-" + id);
    if (error) return errorDiv.textContent = error;

    const updated = {
        id,
        title: title.trim(),
        category: category.trim(),
        location: location.trim(),
        date,
        capacity: parseInt(capacityStr),
        description: description.trim()
    };

    try {
        const res = await fetch(API_EVENTS + "?action=update", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(updated)
        });

        const data = await res.json();
        if (!data.success) return alert("Erreur lors de la mise à jour.");

        await fetchEvents();
        openedEditId = null;

    } catch (err) { console.error("Erreur UPDATE :", err); }
}


/**************************************************
 * RENDER INITIATIVES TABLE
 **************************************************/
function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("fr-FR", {
        year: "numeric", month: "long", day: "numeric"
    });
}

function renderInitiativesTable() {
    const tbody = document.getElementById("initiatives-table-body");
    if (!tbody) return;
    tbody.innerHTML = "";

    events.forEach(event => {
        const tr = document.createElement("tr");
        tr.id = "row-" + event.id;

        tr.innerHTML = `
            <td>${event.title}</td>
            <td>${event.created_by || "Organisation"}</td>
            <td>${event.category}</td>
            <td>${event.location}</td>
            <td>${formatDate(event.date)}</td>
            <td>
                <span class="status-badge ${event.status}">
                    ${event.status === "validé" ? "Validée" :
                      event.status === "en_attente" ? "En attente" : "Refusée"}
                </span>
            </td>
            <td class="actions-cell">
                <button class="action-btn" onclick="openInlineEditor(${event.id})">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="action-btn danger" onclick="deleteEvent(${event.id})">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <button class="action-btn success" onclick="setEventStatus(${event.id}, 'validé')">
                    <i class="fa-solid fa-check"></i>
                </button>
                <button class="action-btn danger" onclick="setEventStatus(${event.id}, 'refusé')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
    });
}


/**************************************************
 * DASHBOARD STATS
 **************************************************/
function updateDashboardStats() {
    const active = events.filter(e => e.status === "validé").length;
    const pending = events.filter(e => e.status === "en_attente").length;

    document.getElementById("stat-active-initiatives").textContent = active;
    document.getElementById("stat-pending-initiatives").textContent = pending;
}


/**************************************************
 * PARTICIPATIONS CRUD
 **************************************************/
function fillParticipationEventSelect() {
    const select = document.getElementById("p-initiative");
    if (!select) return;
    select.innerHTML = "";
    events.forEach(evt => {
        const opt = document.createElement("option");
        opt.value = evt.id;
        opt.textContent = evt.title;
        select.appendChild(opt);
    });
}

function renderParticipationList() {
    const container = document.getElementById("participation-list");
    if (!container) return;

    if (!participations.length) {
        container.innerHTML = "<p>Aucune participation pour le moment.</p>";
        return;
    }

    container.innerHTML = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Initiative</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${participations.map(p => `
                    <tr>
                        <td>${p.event_title || ""}</td>
                        <td>${p.fullname}</td>
                        <td>${p.email}</td>
                        <td>${p.message || ""}</td>
                        <td class="actions-cell">
                            <button class="action-btn" onclick="editParticipation(${p.id})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="action-btn danger" onclick="deleteParticipation(${p.id})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>`).join("")}
            </tbody>
        </table>`;
}

function setupParticipationUI() {
    const btnAdd = document.getElementById("btn-add-participation");
    const btnCancel = document.getElementById("btn-cancel-participation");
    const formContainer = document.getElementById("participation-form-container");
    const form = document.getElementById("participation-form");

    if (btnAdd) {
        btnAdd.addEventListener("click", () => {
            editingParticipationId = null;
            document.getElementById("p-fullname").value = "";
            document.getElementById("p-email").value = "";
            document.getElementById("p-message").value = "";
            document.getElementById("p-id").value = "";
            document.getElementById("participation-error").textContent = "";
            document.getElementById("participation-form-title").textContent = "Nouvelle participation";
            formContainer.classList.remove("hidden");
        });
    }

    if (btnCancel) {
        btnCancel.addEventListener("click", () => formContainer.classList.add("hidden"));
    }

    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const fullname = document.getElementById("p-fullname").value;
            const email = document.getElementById("p-email").value;
            const message = document.getElementById("p-message").value;
            const event_id = document.getElementById("p-initiative").value;
            const errP = document.getElementById("participation-error");

            let error = "";
            error += validateText(fullname, "Nom complet");
            error += validateEmail(email);

            if (error) return errP.textContent = error;

            const payload = {
                id: editingParticipationId,
                fullname: fullname.trim(),
                email: email.trim(),
                message: message.trim(),
                event_id
            };

            const action = editingParticipationId ? "update" : "create";

            try {
                const res = await fetch(API_PARTICIPATIONS + "?action=" + action, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!data.success) return errP.textContent = "Erreur lors de l'enregistrement.";

                await fetchParticipations();
                formContainer.classList.add("hidden");

            } catch (err) {
                errP.textContent = "Erreur réseau.";
            }
        });
    }
}

function editParticipation(id) {
    const p = participations.find(x => String(x.id) === String(id));
    if (!p) return;

    editingParticipationId = id;

    document.getElementById("p-initiative").value = p.event_id;
    document.getElementById("p-fullname").value = p.fullname;
    document.getElementById("p-email").value = p.email;
    document.getElementById("p-message").value = p.message || "";
    document.getElementById("p-id").value = p.id;

    document.getElementById("participation-form-title").textContent = "Modifier la participation";
    document.getElementById("participation-error").textContent = "";

    document.getElementById("participation-form-container").classList.remove("hidden");
}

async function deleteParticipation(id) {
    if (!confirm("Supprimer cette participation ?")) return;

    try {
        const res = await fetch(API_PARTICIPATIONS + "?action=delete", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (!data.success) return alert("Erreur suppression participation.");
        await fetchParticipations();
    } catch (err) { console.error("Erreur suppression participation :", err); }
}


/**************************************************
 * HISTOIRES (REVIEW + DELETE)
 **************************************************/
async function fetchStories() {
    const errorBox = document.getElementById("stories-error");
    if (errorBox) errorBox.textContent = "";

    try {
        const res = await fetch(API_STORIES + "?action=list");
        const data = await res.json();

        if (!data.success) {
            if (errorBox) errorBox.textContent = data.error || "Accès refusé.";
            renderStoriesTable([]);
            return;
        }

        stories = data.stories || [];
        renderStoriesTable(stories);
    } catch (err) {
        console.error("Erreur chargement histoires :", err);
        if (errorBox) errorBox.textContent = "Erreur réseau lors du chargement.";
    }
}

function renderStoriesTable(list = stories) {
    const tbody = document.getElementById("stories-table-body");
    if (!tbody) return;

    if (!list.length) {
        tbody.innerHTML = "<tr><td colspan='5'>Aucune histoire pour le moment.</td></tr>";
        return;
    }

    tbody.innerHTML = list.map(story => `
        <tr id="story-row-${story.id}">
            <td>${story.titre || ""}</td>
            <td>${story.auteur_nom || "Inconnu"}</td>
            <td>${formatDate(story.date_publication)}</td>
            <td>
                <span class="status-badge ${story.statut === "publiee" ? "active" : "inactive"}">
                    ${story.statut || ""}
                </span>
            </td>
            <td class="actions-cell">
                <button class="action-btn" title="Review" onclick="toggleStoryReview(${story.id})">
                    <i class="fa-solid fa-eye"></i>
                </button>
                <button class="action-btn danger" title="Supprimer" onclick="deleteStoryAdmin(${story.id})">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join("");
}

function toggleStoryReview(id) {
    const existing = document.getElementById("story-details-" + id);
    if (existing) {
        existing.remove();
        return;
    }

    const story = stories.find(s => String(s.id) === String(id));
    const row = document.getElementById("story-row-" + id);
    if (!story || !row) return;

    const detailRow = document.createElement("tr");
    detailRow.id = "story-details-" + id;
    detailRow.innerHTML = `
        <td colspan="5">
            <div class="story-detail-box">
                <div class="story-detail-header">
                    <h4>${story.titre || "Sans titre"}</h4>
                    <span>${story.auteur_nom || "Inconnu"} • ${formatDate(story.date_publication)}</span>
                </div>
                <p>${(story.contenu || "").replace(/\n/g, "<br>")}</p>
            </div>
        </td>`;
    row.after(detailRow);
}

async function deleteStoryAdmin(id) {
    if (!confirm("Supprimer définitivement cette histoire ?")) return;

    try {
        const res = await fetch(API_STORIES + "?action=delete", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (!data.success) return alert("Erreur lors de la suppression.");
        await fetchStories();
    } catch (err) {
        console.error("Erreur suppression histoire :", err);
    }
}

function setupStoriesUI() {
    const refreshBtn = document.getElementById("btn-review-stories");
    if (refreshBtn) {
        refreshBtn.addEventListener("click", fetchStories);
    }
}


/**************************************************
 * NAVIGATION
 **************************************************/
function setupNavigation() {
    const navItems = document.querySelectorAll(".nav-item");
    const pages = document.querySelectorAll(".page-content");

    navItems.forEach(item => {
        item.addEventListener("click", (e) => {
            const pageName = item.dataset.page;
            if (!pageName) return; // liens externes : on laisse le comportement par défaut
            e.preventDefault();

            navItems.forEach(n => n.classList.remove("active"));
            item.classList.add("active");

            pages.forEach(p => p.classList.remove("active"));
            document.getElementById("page-" + pageName).classList.add("active");
        });
    });
}

function setupSidebarToggle() {
    const menuToggle = document.getElementById("menu-toggle");
    const sidebar = document.querySelector(".sidebar");
    if (!menuToggle || !sidebar) return;

    menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
    });
}


/**************************************************
 * INIT
 **************************************************/
document.addEventListener("DOMContentLoaded", async () => {
    setupNavigation();
    setupSidebarToggle();
    setupParticipationUI();
    setupStoriesUI();
    await fetchEvents();
    await fetchParticipations();
    await fetchStories();
});
