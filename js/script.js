// Sample assignment data
const assignments = [
    {
        id: 1,
        title: "History and Development of X-Rays",
        subject: "PF",
        dueDate: "07-Jul-2023",
        totalMarks: 15,
        obtainedMarks: "N/A",
        status: "out-of-time"
    },
    {
        id: 2,
        title: "Classify transactions based on their nature",
        subject: "CA",
        dueDate: "05-Jul-2023",
        totalMarks: 12,
        obtainedMarks: "N/A",
        status: "out-of-time"
    },
    {
        id: 3,
        title: "Boolean Algebra and Logic Gates",
        subject: "DLD",
        dueDate: "12-Jul-2023",
        totalMarks: 20,
        obtainedMarks: "N/A",
        status: "submit"
    }
];

// Function to populate the assignments table
function populateAssignmentsTable() {
    const tableBody = document.getElementById('assignmentTableBody');
    if (tableBody) {
        tableBody.innerHTML = '';

        assignments.forEach(assignment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${assignment.id}</td>
                <td>${assignment.title}</td>
                <td>${assignment.subject}</td>
                <td>${assignment.dueDate}</td>
                <td>
                    <a href="#" class="text-primary me-2"><i class="fas fa-eye"></i></a>
                    <a href="#" class="text-primary"><i class="fas fa-download"></i></a>
                </td>
                <td>
                    <span class="badge-${assignment.status}">
                        ${assignment.status === 'out-of-time' ? 'Out of time' : 'Submit'}
                    </span>
                </td>
                <td>${assignment.totalMarks}</td>
                <td>${assignment.obtainedMarks}</td>
                <td>
                    <span class="badge-${assignment.status}">
                        ${assignment.status === 'out-of-time' ? 'Out of time' : 'Submit'}
                    </span>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }
}

// Initialize sidebar functionality
function initializeSidebar() {
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    
    // Set initial state based on localStorage, default to expanded
    const sidebarState = localStorage.getItem('sidebarState') || 'expanded';
    if (sidebarState === 'collapsed') {
        sidebar.classList.add('collapsed');
        content.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
        content.classList.remove('collapsed');
    }
    
    // Handle sidebar toggle
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('collapsed');
            
            // Save state to localStorage
            localStorage.setItem('sidebarState', 
                sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded'
            );
        });
    }

    // Handle submenu state
    const dropdownToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            if (sidebar.classList.contains('collapsed')) {
                e.preventDefault();
                e.stopPropagation();
                
                // Remove any existing expanded submenus
                document.querySelectorAll('.collapse.show').forEach(submenu => {
                    if (submenu !== toggle.nextElementSibling) {
                        submenu.classList.remove('show');
                    }
                });
                
                const submenu = toggle.nextElementSibling;
                submenu.classList.toggle('show');
            }
        });
    });

    // Close submenus when clicking outside
    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target)) {
            document.querySelectorAll('.collapse.show').forEach(submenu => {
                submenu.classList.remove('show');
            });
        }
    });

    const sidebarLinks = document.querySelectorAll('#sidebar ul li a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            sidebarLinks.forEach(l => l.parentElement.classList.remove('active'));
            this.parentElement.classList.add('active');
        });
    });
}

// Event listener for when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initializeSidebar();
    populateAssignmentsTable();

    // Handle entries dropdown change
    document.querySelector('.entries-selector select').addEventListener('change', function() {
        // In a real application, this would trigger a reload of the table data
        console.log('Entries per page changed to:', this.value);
    });
});
