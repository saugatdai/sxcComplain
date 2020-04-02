package sxc;

import java.io.BufferedReader;
import java.net.*;
import java.io.*;

public class Server {

	private static ServerSocket socket;

	private static Socket connection;
	private static String command = new String();
	private static String responseStr = new String();

	private static int port = 4309;

	public void startServer() {

		try {
			socket = new ServerSocket(port);
			while (true) {
				connection = socket.accept();
				
				InputStreamReader inputStream = new InputStreamReader(connection.getInputStream());
				DataOutputStream response = new DataOutputStream(connection.getOutputStream());
				BufferedReader input = new BufferedReader(inputStream);

				command = input.readLine();
				if(command.equals("status")){
					responseStr = "running";
				}
				// System.out.println("The input is" + command);
				response.writeBytes(responseStr);
				response.flush();
				response.close();
			}
		} catch (IOException e) {
			System.out.println("Fail!: " + e.toString());
		}

		System.out.println("Closing...");
	}
}
