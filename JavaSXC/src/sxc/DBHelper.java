package sxc;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.time.Instant;
import java.time.ZoneId;
import java.time.ZonedDateTime;
import java.time.format.DateTimeFormatter;
import java.time.format.FormatStyle;
import java.util.ArrayList;
import java.util.Locale;

public class DBHelper {
	private Connection conn = null;
	private Statement stmt = null;
	private PreparedStatement prepStmt = null;
	private ResultSet resultSet = null;

	public void createConnection() {
		String[] info = new PropertiesHandler().readProperties();
		try {
			Class.forName("com.mysql.jdbc.Driver");
			conn = DriverManager.getConnection(
					"jdbc:mysql://" + info[0] + "/sxcSupervision?user=" + info[1] + "&" + "password=" + info[2]);
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	public int getOldComplains() {
		// get the current timestamp
		Instant instant = Instant.now();
		long timeStampSeconds = instant.getEpochSecond();
		// find the value of 12 hrs earlier timestamp..
		timeStampSeconds = timeStampSeconds - 12 * 3600;
		int num = 0;
		try {
			createConnection();
			stmt = conn.createStatement();
			resultSet = stmt.executeQuery("select count(*) from complains where complainDate<'" + timeStampSeconds
					+ "' and status='pending'");
			while (resultSet.next()) {
				num = resultSet.getInt(1);
			}
		} catch (SQLException e) {
			e.printStackTrace();
		}

		return num;
	}

	public ArrayList<String[]> getOldComplainTable() {
		int i = 0;
		ArrayList<String[]> list = new ArrayList<>();
		// prepare time stamps
		Instant instant = Instant.now();
		long timeStampSeconds = instant.getEpochSecond();
		timeStampSeconds = timeStampSeconds - 12 * 3600;
		try {
			createConnection();
			stmt = conn.createStatement();
			resultSet = stmt.executeQuery(
					"select * from complains where complainDate<'" + timeStampSeconds + "' and status='pending' order by id desc");
			while (resultSet.next()) {
				String[] str = new String[10];
				str[0] = resultSet.getString("complainBy");
				str[1] = resultSet.getString("Details");
				str[2] = resultSet.getString("status");
				str[3] = resultSet.getString("handledBy");
				str[4] = resultSet.getString("complainDate");
				str[5] = resultSet.getString("handledDate");
				str[6] = resultSet.getString("remarks");
				str[7] = resultSet.getString("roomNo");
				str[8] = resultSet.getString("roomName");
				str[9] = resultSet.getString("compNo");
				list.add(str);
				System.out.println("Added complain for " + i + list.get(i)[0]);
				++i;
			}
		} catch (SQLException e) {
			e.printStackTrace();
		}
		System.out.println("\n\n\n");
		for (String[] row : list) {
			System.out.println(row[0]);
		}
		return list;
	}
	public ArrayList<String[]> getTodaysActivityTable(){
		int i = 0;
		ArrayList<String[]> list = new ArrayList<>();
		// prepare time stamps
		Instant instant = Instant.now();
		long timeStampSeconds = instant.getEpochSecond();
		timeStampSeconds = timeStampSeconds - 12 * 3600;
		try {
			createConnection();
			stmt = conn.createStatement();
			resultSet = stmt.executeQuery(
					"select * from complains where complainDate>'" + timeStampSeconds + "' order by id desc");
			while (resultSet.next()) {
				String[] str = new String[10];
				str[0] = resultSet.getString("complainBy");
				str[1] = resultSet.getString("Details");
				str[2] = resultSet.getString("status");
				str[3] = resultSet.getString("handledBy");
				str[4] = resultSet.getString("complainDate");
				str[5] = resultSet.getString("handledDate");
				str[6] = resultSet.getString("remarks");
				str[7] = resultSet.getString("roomNo");
				str[8] = resultSet.getString("roomName");
				str[9] = resultSet.getString("compNo");
				list.add(str);
				System.out.println("Added complain for " + i + list.get(i)[0]);
				++i;
			}
		} catch (SQLException e) {
			e.printStackTrace();
		}
		System.out.println("\n\n\n");
		for (String[] row : list) {
			System.out.println(row[0]);
		}
		return list;
	}

	public String prepateHTMLTableOFOldComplains(ArrayList<String[]> datas) {
		StringBuilder sb = new StringBuilder();
		String[] colors = {"#9c9","#fff"};
		int index = 0;
		sb.append("<table border='1' cellspacing='0'><tr><th>S.N</th><th>complain By</th><th>Problem Details</th>"
				+ "<th>Status</th><th>Handled By</th><th>Complain Date</th><th>Handled Date</th>"
				+ "<th>Remarks</th><th>Room No</th><th>Room Name</th><th>Comp No</th>");
		for (int i = 0; i < datas.size(); i++) {
			String[] row = datas.get(i);

			Instant instant;
			ZoneId zoneId;
			ZonedDateTime zdt;
			String complainDate = "";
			String handledDate = "";
			DateTimeFormatter formatter;

			if (row[4] != null) {
				instant = Instant.ofEpochSecond(Integer.parseInt(row[4]));
				zoneId = ZoneId.of ( "Asia/Kathmandu" );
				zdt = instant.atZone ( zoneId );
				formatter = DateTimeFormatter.ofLocalizedDateTime ( FormatStyle.FULL );
				formatter = formatter.withLocale ( Locale.US );
				complainDate = zdt.format ( formatter );
			}
			
			if (row[5] != null) {
				instant = Instant.ofEpochSecond(Integer.parseInt(row[5]));
				zoneId = ZoneId.of ( "Asia/Kathmandu" );
				zdt = instant.atZone ( zoneId );
				formatter = DateTimeFormatter.ofLocalizedDateTime ( FormatStyle.FULL );
				formatter = formatter.withLocale ( Locale.US );
				handledDate = zdt.format ( formatter );
			}

			sb.append("<tr  style='background:"+colors[index]+";'>");
			sb.append("<td>" + (i+1) + ". </td>");
			sb.append("<td>" + row[0] + "</td>");
			sb.append("<td>" + row[1] + "</td>");
			sb.append("<td>" + row[2] + "</td>");
			sb.append("<td>" + row[3] + "</td>");
			sb.append("<td>" + complainDate + "</td>");
			sb.append("<td>" + handledDate + "</td>");
			sb.append("<td>" + row[6] + "</td>");
			sb.append("<td>" + row[7] + "</td>");
			sb.append("<td>" + row[8] + "</td>");
			sb.append("<td>" + row[9] + "</td>");
			sb.append("</tr>");
			index = 1-index;
		}

		return sb.toString();
	}

	public void close() {
		if (conn != null) {
			try {
				conn.close();
			} catch (SQLException e) {
				e.printStackTrace();
			}
		}
		if (stmt != null) {
			try {
				stmt.close();
			} catch (SQLException e) {
				e.printStackTrace();
			}
		}
		if (prepStmt != null) {
			try {
				prepStmt.close();
			} catch (SQLException e) {
				e.printStackTrace();
			}
		}
		if (resultSet != null) {
			try {
				resultSet.close();
			} catch (SQLException e) {
				e.printStackTrace();
			}
		}
	}
}
