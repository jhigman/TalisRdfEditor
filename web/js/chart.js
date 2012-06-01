

function doChart(store, from, to, dataSeries, dataLabels) {

	var chart = new Highcharts.Chart({
		   chart: {
		      renderTo: 'container',
		      defaultSeriesType: 'spline'
		   },
		   title: {
		      text: 'Updates per hour' 
		   },
		   xAxis: {
		      categories: dataLabels,
		      title: {
		         text: 'Hour'
		      },
		      labels: {
		    	  rotation: 270
		      }
		   },
		   yAxis: {
		      title: {
		         text: 'Number of changes'
		      }
		   },
		   legend: {
		      enabled: false
		   },
		   tooltip: {
		      formatter: function() {
		                return '<b>'+ this.series.name +'</b><br/>'+
		            this.x +':00 - '+ this.y + ' changes';
		      }
		   },
		   plotOptions: {
		      spline: {
		         cursor: 'pointer',
		         point: {
		            events: {
		               click: function() {
		                  alert ('this.category: '+ this.category +'\nthis.y: '+ this.y);
		               }
		            }
		         }
		      }
		   },
		   series: [{
		      name: store,
		      data: dataSeries
		   }]
		});

}
