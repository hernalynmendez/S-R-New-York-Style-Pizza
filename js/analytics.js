document.addEventListener('DOMContentLoaded', function(){
    // Sample data
    const labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    const current = [820, 920, 1020, 1250, 1340, 8240, 980];
    const previous = [720, 820, 920, 1100, 1200, 1600, 900];

    // Main revenue chart
    const ctx = document.getElementById('revenueChart');
    if (ctx && window.Chart) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {label: 'Current', data: current, borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,0.12)', tension:0.35, pointRadius:3, fill:true},
                    {label: 'Previous', data: previous, borderColor: 'rgba(255,255,255,0.12)', backgroundColor: 'transparent', borderDash:[6,6], tension:0.35, pointRadius:0}
                ]
            },
            options: {
                responsive:true,maintainAspectRatio:false,
                plugins:{legend:{display:true,labels:{color:'#cbd5e1'}} ,tooltip:{mode:'index',intersect:false}},
                scales:{x:{ticks:{color:'#cbd5e1'}},y:{ticks:{color:'#cbd5e1'},grid:{color:'rgba(255,255,255,0.03)'}}
            }
        });
    }

    // Donut chart
    const dctx = document.getElementById('donutChart');
    if (dctx && window.Chart) {
        new Chart(dctx, {
            type:'doughnut',
            data:{labels:['Online Sales','Subscription','Enterprise'], datasets:[{data:[62,24,14],backgroundColor:['#3B82F6','#8b5cf6','#06b6d4']}]},
            options:{plugins:{legend:{position:'bottom',labels:{color:'#cbd5e1'}}},responsive:true,maintainAspectRatio:false}
        });
    }
});
