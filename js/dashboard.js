// Function to convert numbers to Khmer numerals
function toKhmerNumeral(number) {
    const khmerNumerals = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
    return number.toString().replace(/[0-9]/g, digit => khmerNumerals[digit]);
}

// Function to fetch and update all dashboard data
function updateDashboard() {
    fetch('api/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            // Update statistics
            document.getElementById('totalStudents').textContent = toKhmerNumeral(data.totalStudents || 0);
            document.getElementById('totalTeachers').textContent = toKhmerNumeral(data.totalTeachers || 0);
            document.getElementById('totalClasses').textContent = toKhmerNumeral(data.totalClasses || 0);
            document.getElementById('pendingAssignments').textContent = toKhmerNumeral(data.pendingAssignments || 0);

            // Update activities
            const activitiesContainer = document.getElementById('recentActivities');
            activitiesContainer.innerHTML = '';
            
            if (data.activities && data.activities.length > 0) {
                data.activities.forEach(activity => {
                    const row = document.createElement('tr');
                    const khmerStatus = activity.status === 'completed' ? 'បានបញ្ចប់' : 'កំពុងដំណើរការ';
                    const badgeClass = activity.status === 'completed' ? 'bg-success' : 'bg-warning';
                    
                    row.innerHTML = `
                        <td>${activity.description}</td>
                        <td>${new Date(activity.date).toLocaleDateString('km-KH')}</td>
                        <td><span class="badge ${badgeClass}">${khmerStatus}</span></td>
                    `;
                    activitiesContainer.appendChild(row);
                });
            } else {
                activitiesContainer.innerHTML = '<tr><td colspan="3" class="text-center">មិនមានសកម្មភាពថ្មីៗទេ</td></tr>';
            }

            // Update events
            const eventsContainer = document.getElementById('upcomingEvents');
            eventsContainer.innerHTML = '';
            
            if (data.events && data.events.length > 0) {
                data.events.forEach(event => {
                    const eventDiv = document.createElement('div');
                    eventDiv.className = 'mb-3 p-2 border-bottom';
                    eventDiv.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">${event.title}</h6>
                            <small class="text-muted">${new Date(event.date).toLocaleDateString('km-KH')}</small>
                        </div>
                        <small class="text-muted">${event.description}</small>
                    `;
                    eventsContainer.appendChild(eventDiv);
                });
            } else {
                eventsContainer.innerHTML = '<div class="text-center">មិនមានព្រឹត្តិការណ៍ខាងមុខទេ</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            // Show error message in Khmer
            document.getElementById('totalStudents').textContent = 'មានបញ្ហា';
            document.getElementById('totalTeachers').textContent = 'មានបញ្ហា';
            document.getElementById('totalClasses').textContent = 'មានបញ្ហា';
            document.getElementById('pendingAssignments').textContent = 'មានបញ្ហា';
        });
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    updateDashboard();
    
    // Refresh data every 5 minutes
    setInterval(updateDashboard, 300000);
});
