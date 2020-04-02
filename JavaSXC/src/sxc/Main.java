package sxc;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Timer;
import java.util.TimerTask;
import java.util.concurrent.TimeUnit;

public class Main {
	public static void main(String[] args) {
		Calendar today = Calendar.getInstance();
		today.set(Calendar.HOUR_OF_DAY, 18);
		today.set(Calendar.MINUTE, 0);
		today.set(Calendar.SECOND, 0);
		
		TimerTask task1 = new TimerTask() {

			@Override
			public void run() {
				ArrayList<String[]> list = new DBHelper().getOldComplainTable();
				if (list.size() > 0) {
					String table = new DBHelper().prepateHTMLTableOFOldComplains(list);
					LogEmailSender.send(table, new PropertiesHandler().getHODEmail(),"SXC Supervision System");
				}
			}
		};
		
		TimerTask task2 = new TimerTask() {
			
			@Override
			public void run() {
				ArrayList<String[]> list = new DBHelper().getTodaysActivityTable();
				if (list.size() > 0) {
					String table = new DBHelper().prepateHTMLTableOFOldComplains(list);
					LogEmailSender.send(table, new PropertiesHandler().getHODEmail(),"Todays Activities");
				}
			}
		};
		Timer timer1 = new Timer();
		// scheduling the task at interval
		timer1.schedule(task1, 0, 1000 * 60*60*4);
		
		Timer timer2 = new Timer();
		timer2.schedule(task2, today.getTime(), TimeUnit.MILLISECONDS.convert(1, TimeUnit.DAYS)); // period: 1 day
		
		new Server().startServer();
	}

}