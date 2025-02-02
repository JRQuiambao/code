const express = require('express');
const { createServer } = require('http');
const { Server } = require('socket.io');
require('dotenv').config();

const app = express();
const server = createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
    }
});

const PORT = process.env.PORT || 3000;

let rooms = {}; // Tracks room states

io.on('connection', (socket) => {
    console.log('User connected:', socket.id);

    socket.on('joinRoom', ({ roomId, username }) => {
        socket.join(roomId);
        console.log(`${username} joined room ${roomId}`);

        if (!rooms[roomId]) {
            rooms[roomId] = {
                users: {},
                currentQuestionIndex: 0,
                totalUsers: 0,
                answeredUsers: new Set(),
                answers: {},
            };
        }

        rooms[roomId].users[socket.id] = { username, answered: false };
        rooms[roomId].totalUsers = Object.keys(rooms[roomId].users).length;

        io.to(roomId).emit('roomUpdate', {
            answeredCount: rooms[roomId].answeredUsers.size,
            totalUsers: rooms[roomId].totalUsers,
            currentQuestionIndex: rooms[roomId].currentQuestionIndex,
        });
    });

    socket.on('chatMessage', ({ roomId, username, message }) => {
        io.to(roomId).emit('message', { username, message });
    });

    socket.on('submitAnswer', ({ roomId, questionId, selectedOption }) => {
        if (!rooms[roomId]) return;

        if (!rooms[roomId].answers[questionId]) {
            rooms[roomId].answers[questionId] = {};
        }

        rooms[roomId].answers[questionId][selectedOption] =
            (rooms[roomId].answers[questionId][selectedOption] || 0) + 1;

        rooms[roomId].answeredUsers.add(socket.id);

        io.to(roomId).emit('roomUpdate', {
            answeredCount: rooms[roomId].answeredUsers.size,
            totalUsers: rooms[roomId].totalUsers,
            currentQuestionIndex: rooms[roomId].currentQuestionIndex,
        });
    });

    socket.on('submitAssessment', ({ roomId, username }) => {
        console.log(`${username} submitted the assessment in room ${roomId}`);
        io.to(roomId).emit('assessmentSubmitted', {
            message: 'Assessment Submitted! All participants can now return to the dashboard.',
        });
    });

    socket.on('nextQuestion', ({ roomId }) => {
        if (!rooms[roomId]) return;

        rooms[roomId].answeredUsers.clear();
        rooms[roomId].currentQuestionIndex++;
        io.to(roomId).emit('roomUpdate', {
            answeredCount: 0,
            totalUsers: rooms[roomId].totalUsers,
            currentQuestionIndex: rooms[roomId].currentQuestionIndex,
        });
    });

    socket.on('fetchAnswers', ({ roomId }) => {
        if (!rooms[roomId]) return;
        io.to(roomId).emit('answerData', rooms[roomId].answers);
    });

    socket.on('disconnect', () => {
        for (const roomId in rooms) {
            if (rooms[roomId].users[socket.id]) {
                delete rooms[roomId].users[socket.id];
                rooms[roomId].totalUsers = Object.keys(rooms[roomId].users).length;

                rooms[roomId].answeredUsers.delete(socket.id);

                io.to(roomId).emit('roomUpdate', {
                    answeredCount: rooms[roomId].answeredUsers.size,
                    totalUsers: rooms[roomId].totalUsers,
                    currentQuestionIndex: rooms[roomId].currentQuestionIndex,
                });
            }
        }
    });
});

server.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
