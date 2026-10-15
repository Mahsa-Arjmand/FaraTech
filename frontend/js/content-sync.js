/**
 * FaraTech Content Sync Script
 * Syncs content from the admin panel database to the frontend
 */

const API_BASE = 'http://localhost:8001/api';

// Cache for content
const contentCache = {};
const projectsCache = [];
const positionsCache = [];
const commentsCache = {};

/**
 * Fetch content for a specific page
 * @param {string} page - Page name (e.g., 'index', 'about', 'services')
 * @returns {Promise<Object>} - Content grouped by section
 */
async function fetchPageContent(page) {
    if (contentCache[page]) {
        return contentCache[page];
    }

    try {
        const response = await fetch(`${API_BASE}/public_content.php?page=${page}`);
        const data = await response.json();

        if (data.success) {
            contentCache[page] = data.data;
            return data.data;
        }
    } catch (error) {
        console.error('Error fetching page content:', error);
    }

    return {};
}

/**
 * Update page content in the DOM
 * @param {string} page - Page name
 */
async function updatePageContent(page) {
    const content = await fetchPageContent(page);

    // Update elements with data-content attributes
    document.querySelectorAll('[data-content]').forEach(element => {
        const key = element.getAttribute('data-content');
        const section = element.getAttribute('data-section') || 'hero';

        if (content[section] && content[section][key]) {
            element.textContent = content[section][key];
        }
    });

    // Update elements with data-content-html attributes (innerHTML)
    document.querySelectorAll('[data-content-html]').forEach(element => {
        const key = element.getAttribute('data-content-html');
        const section = element.getAttribute('data-section') || 'hero';

        if (content[section] && content[section][key]) {
            element.innerHTML = content[section][key];
        }
    });
}

/**
 * Fetch published projects
 * @returns {Promise<Array>} - Array of projects
 */
async function fetchProjects() {
    if (projectsCache.length > 0) {
        return projectsCache;
    }

    try {
        const response = await fetch(`${API_BASE}/public_projects.php`);
        const data = await response.json();

        if (data.success) {
            projectsCache.length = 0;
            projectsCache.push(...data.data);
            return data.data;
        }
    } catch (error) {
        console.error('Error fetching projects:', error);
    }

    return [];
}

/**
 * Render projects to the DOM
 * @param {string} containerSelector - CSS selector for container
 * @param {Function} renderFunction - Custom render function
 */
async function renderProjects(containerSelector, renderFunction) {
    const projects = await fetchProjects();
    const container = document.querySelector(containerSelector);

    if (!container) return;

    container.innerHTML = '';

    projects.forEach(project => {
        if (renderFunction) {
            container.appendChild(renderFunction(project));
        }
    });
}

/**
 * Fetch team positions
 * @returns {Promise<Array>} - Array of positions
 */
async function fetchTeamPositions() {
    if (positionsCache.length > 0) {
        return positionsCache;
    }

    try {
        const response = await fetch(`${API_BASE}/team_positions.php?action=public_list`);
        const data = await response.json();

        if (data.success) {
            positionsCache.length = 0;
            positionsCache.push(...data.data);
            return data.data;
        }
    } catch (error) {
        console.error('Error fetching team positions:', error);
    }

    return [];
}

/**
 * Render team positions to the DOM
 * @param {string} containerSelector - CSS selector for container
 * @param {Function} renderFunction - Custom render function
 */
async function renderTeamPositions(containerSelector, renderFunction) {
    const positions = await fetchTeamPositions();
    const container = document.querySelector(containerSelector);

    if (!container) return;

    container.innerHTML = '';

    positions.forEach(position => {
        if (renderFunction) {
            container.appendChild(renderFunction(position));
        }
    });
}

/**
 * Fetch comments for a page
 * @param {string} page - Page name
 * @returns {Promise<Array>} - Array of comments
 */
async function fetchComments(page) {
    if (commentsCache[page]) {
        return commentsCache[page];
    }

    try {
        const response = await fetch(`${API_BASE}/comments.php?action=public_list&page=${page}`);
        const data = await response.json();

        if (data.success) {
            commentsCache[page] = data.data;
            return data.data;
        }
    } catch (error) {
        console.error('Error fetching comments:', error);
    }

    return [];
}

/**
 * Render comments to the DOM
 * @param {string} page - Page name
 * @param {string} containerSelector - CSS selector for container
 * @param {Function} renderFunction - Custom render function
 */
async function renderComments(page, containerSelector, renderFunction) {
    const comments = await fetchComments(page);
    const container = document.querySelector(containerSelector);

    if (!container) return;

    container.innerHTML = '';

    comments.forEach(comment => {
        if (renderFunction) {
            container.appendChild(renderFunction(comment));
        }
    });
}

/**
 * Submit a comment
 * @param {Object} commentData - Comment data
 * @returns {Promise<boolean>} - Success status
 */
async function submitComment(commentData) {
    try {
        const response = await fetch(`${API_BASE}/comments.php?action=public_create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(commentData)
        });

        const data = await response.json();

        if (data.success) {
            // Clear cache for this page
            delete commentsCache[commentData.page];
            return true;
        }
    } catch (error) {
        console.error('Error submitting comment:', error);
    }

    return false;
}

/**
 * Submit a job application
 * @param {Object} applicationData - Application data
 * @returns {Promise<boolean>} - Success status
 */
async function submitJobApplication(applicationData) {
    try {
        const response = await fetch(`${API_BASE}/job_applications.php?action=public_create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(applicationData)
        });

        const data = await response.json();

        if (data.success) {
            return true;
        }
    } catch (error) {
        console.error('Error submitting job application:', error);
    }

    return false;
}

/**
 * Track page view
 * @param {string} page - Page name
 */
function trackPageView(page) {
    fetch(`${API_BASE}/page_views.php?action=track&page=${page}`)
        .catch(error => console.error('Error tracking page view:', error));
}

/**
 * Initialize content sync for current page
 */
function initContentSync() {
    // Get current page name from URL
    const path = window.location.pathname;
    const page = path.split('/').pop().replace('.html', '') || 'index';

    // Track page view
    trackPageView(page);

    // Update page content
    updatePageContent(page);

    // Load page-specific content
    if (page === 'portfolio') {
        // Projects are loaded by portfolio page logic
    } else if (page === 'team') {
        // Team positions are loaded by team page logic
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initContentSync);
} else {
    initContentSync();
}

// Export functions for manual use
window.FaraTechSync = {
    fetchPageContent,
    updatePageContent,
    fetchProjects,
    renderProjects,
    fetchTeamPositions,
    renderTeamPositions,
    fetchComments,
    renderComments,
    submitComment,
    submitJobApplication,
    trackPageView,
    initContentSync
};
